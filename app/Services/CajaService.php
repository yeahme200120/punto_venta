<?php
// app/Services/CajaService.php
namespace App\Services;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Models\CajaTransferencia;
use App\Models\ContraseñaMaestra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CajaService
{
    /**
     * Verificar si se puede realizar una operación (requiere caja abierta)
     */
    public static function verificarCajaAbierta($sucursalId, $userId)
    {
        // Buscar caja abierta del día
        $apertura = CajaApertura::where('sucursal_id', $sucursalId)
            ->where('user_id', $userId)
            ->where('estado', 'abierta')
            ->whereDate('fecha', today())
            ->first();
        
        if ($apertura) {
            return $apertura;
        }
        
        // Buscar caja abierta de días anteriores (fin de semana)
        $aperturaAnterior = CajaApertura::where('sucursal_id', $sucursalId)
            ->where('user_id', $userId)
            ->where('estado', 'abierta')
            ->orderBy('fecha', 'desc')
            ->first();
        
        if ($aperturaAnterior) {
            throw new \Exception("Tienes una caja abierta del día {$aperturaAnterior->fecha->format('d/m/Y')}. Debes cerrarla primero.");
        }
        
        return null;
    }
    
    /**
     * Abrir caja
     */
    public static function abrirCaja($cajaId, $userId, $sucursalId, $montoInicial, $observaciones = null)
    {
        DB::beginTransaction();
        try {
            $caja = Caja::findOrFail($cajaId);
            
            // Verificar si ya tiene apertura abierta
            if ($caja->tieneAperturaAbierta()) {
                throw new \Exception('Esta caja ya tiene una apertura abierta.');
            }
            
            // Verificar múltiples aperturas
            $aperturasHoy = CajaApertura::where('caja_id', $cajaId)
                ->whereDate('fecha', today())
                ->count();
            
            if ($aperturasHoy > 0 && !$caja->permite_multiple) {
                throw new \Exception('Esta caja no permite múltiples aperturas en el mismo día.');
            }
            
            $apertura = CajaApertura::create([
                'caja_id' => $cajaId,
                'user_id' => $userId,
                'sucursal_id' => $sucursalId,
                'fecha' => today(),
                'fecha_apertura' => now(),
                'monto_inicial' => $montoInicial,
                'observaciones_apertura' => $observaciones,
                'estado' => 'abierta'
            ]);
            
            // Registrar movimiento de apertura
            CajaMovimiento::create([
                'caja_apertura_id' => $apertura->id,
                'user_id' => $userId,
                'sucursal_id' => $sucursalId,
                'tipo' => 'ingreso',
                'categoria' => 'ajuste',
                'forma_pago' => 'efectivo',
                'monto' => $montoInicial,
                'concepto' => 'Apertura de caja - Fondo inicial'
            ]);
            
            DB::commit();
            return $apertura;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Cerrar caja
     */
    public static function cerrarCaja($aperturaId, $montoFinal, $observaciones = null)
    {
        DB::beginTransaction();
        try {
            $apertura = CajaApertura::findOrFail($aperturaId);
            
            if ($apertura->estado !== 'abierta') {
                throw new \Exception('Esta caja ya está cerrada.');
            }
            
            $saldoCalculado = $apertura->saldoActual();
            
            if (abs($saldoCalculado - $montoFinal) > 0.01) {
                throw new \Exception("El monto final no coincide con el saldo calculado. Saldo actual: $" . number_format($saldoCalculado, 2));
            }
            
            $apertura->cerrar($montoFinal, $observaciones);
            
            DB::commit();
            return $apertura;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Registrar movimiento
     */
    public static function registrarMovimiento($aperturaId, $userId, $data, $requiereAutorizacion = false)
    {
        DB::beginTransaction();
        try {
            $apertura = CajaApertura::findOrFail($aperturaId);
            
            if ($apertura->estado !== 'abierta') {
                throw new \Exception('La caja no está abierta.');
            }
            
            $movimiento = CajaMovimiento::create([
                'caja_apertura_id' => $aperturaId,
                'user_id' => $userId,
                'sucursal_id' => $apertura->sucursal_id,
                'tipo' => $data['tipo'],
                'categoria' => $data['categoria'],
                'forma_pago' => $data['forma_pago'],
                'monto' => $data['monto'],
                'referencia' => $data['referencia'] ?? null,
                'concepto' => $data['concepto'],
                'comprobante' => $data['comprobante'] ?? null,
                'referencia_id' => $data['referencia_id'] ?? null,
                'referencia_type' => $data['referencia_type'] ?? null,
                'requiere_autorizacion' => $requiereAutorizacion
            ]);
            
            // Actualizar totales de la apertura
            if ($data['tipo'] === 'ingreso') {
                $apertura->increment('total_ingresos', $data['monto']);
            } else {
                $apertura->increment('total_egresos', $data['monto']);
            }
            
            DB::commit();
            return $movimiento;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Autorizar movimiento
     */
    public static function autorizarMovimiento($movimientoId, $autorizadorId, $passwordMaestra)
    {
        DB::beginTransaction();
        try {
            $movimiento = CajaMovimiento::findOrFail($movimientoId);
            $autorizador = \App\Models\User::findOrFail($autorizadorId);
            
            // Verificar contraseña maestra
            $tipo = $autorizador->hasRole('Super Admin') ? 'super_admin' : 'admin';
            $passwordMaestraDB = ContraseñaMaestra::where('user_id', $autorizadorId)
                ->where('tipo', $tipo)
                ->where('activo', true)
                ->first();
            
            if (!$passwordMaestraDB || !$passwordMaestraDB->verificar($passwordMaestra)) {
                throw new \Exception('Contraseña maestra incorrecta.');
            }
            
            // Actualizar password maestra (último uso)
            $passwordMaestraDB->update(['ultimo_uso' => now()]);
            
            $movimiento->update([
                'requiere_autorizacion' => false,
                'autorizado_por' => $autorizadorId,
                'autorizado_en' => now()
            ]);
            
            DB::commit();
            return $movimiento;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Transferir entre cajas
     */
    public static function transferirEntreCajas($origenId, $destinoId, $userId, $monto, $motivo)
    {
        DB::beginTransaction();
        try {
            $aperturaOrigen = CajaApertura::where('caja_id', $origenId)
                ->where('estado', 'abierta')
                ->first();
            
            $aperturaDestino = CajaApertura::where('caja_id', $destinoId)
                ->where('estado', 'abierta')
                ->first();
            
            if (!$aperturaOrigen || !$aperturaDestino) {
                throw new \Exception('Ambas cajas deben estar abiertas.');
            }
            
            $transferencia = CajaTransferencia::create([
                'caja_origen_id' => $origenId,
                'caja_destino_id' => $destinoId,
                'caja_apertura_origen_id' => $aperturaOrigen->id,
                'caja_apertura_destino_id' => $aperturaDestino->id,
                'user_id' => $userId,
                'monto' => $monto,
                'motivo' => $motivo,
                'estado' => 'pendiente'
            ]);
            
            DB::commit();
            return $transferencia;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Aprobar transferencia
     */
    public static function aprobarTransferencia($transferenciaId, $autorizadorId, $passwordMaestra)
    {
        DB::beginTransaction();
        try {
            $transferencia = CajaTransferencia::findOrFail($transferenciaId);
            $autorizador = \App\Models\User::findOrFail($autorizadorId);
            
            // Verificar contraseña maestra
            $tipo = $autorizador->hasRole('Super Admin') ? 'super_admin' : 'admin';
            $passwordMaestraDB = ContraseñaMaestra::where('user_id', $autorizadorId)
                ->where('tipo', $tipo)
                ->where('activo', true)
                ->first();
            
            if (!$passwordMaestraDB || !$passwordMaestraDB->verificar($passwordMaestra)) {
                throw new \Exception('Contraseña maestra incorrecta.');
            }
            
            // Registrar egreso en caja origen
            CajaMovimiento::create([
                'caja_apertura_id' => $transferencia->caja_apertura_origen_id,
                'user_id' => $autorizadorId,
                'sucursal_id' => $transferencia->cajaOrigen->sucursal_id,
                'tipo' => 'egreso',
                'categoria' => 'transferencia',
                'forma_pago' => 'efectivo',
                'monto' => $transferencia->monto,
                'concepto' => "Transferencia a caja: {$transferencia->cajaDestino->nombre} - {$transferencia->motivo}"
            ]);
            
            // Registrar ingreso en caja destino
            CajaMovimiento::create([
                'caja_apertura_id' => $transferencia->caja_apertura_destino_id,
                'user_id' => $autorizadorId,
                'sucursal_id' => $transferencia->cajaDestino->sucursal_id,
                'tipo' => 'ingreso',
                'categoria' => 'transferencia',
                'forma_pago' => 'efectivo',
                'monto' => $transferencia->monto,
                'concepto' => "Transferencia desde caja: {$transferencia->cajaOrigen->nombre} - {$transferencia->motivo}"
            ]);
            
            $transferencia->update([
                'estado' => 'aprobada',
                'autorizado_por' => $autorizadorId,
                'autorizado_en' => now()
            ]);
            
            $passwordMaestraDB->update(['ultimo_uso' => now()]);
            
            DB::commit();
            return $transferencia;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtener resumen del día
     */
    public static function resumenDia($aperturaId)
    {
        $apertura = CajaApertura::with('movimientos')->findOrFail($aperturaId);
        
        $resumen = [
            'fecha' => $apertura->fecha->format('d/m/Y'),
            'apertura' => $apertura->fecha_apertura->format('H:i'),
            'cierre' => $apertura->fecha_cierre ? $apertura->fecha_cierre->format('H:i') : 'Abierta',
            'monto_inicial' => $apertura->monto_inicial,
            'total_ingresos' => $apertura->total_ingresos,
            'total_egresos' => $apertura->total_egresos,
            'saldo_esperado' => $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos,
            'saldo_real' => $apertura->monto_final,
            'por_forma_pago' => [
                'efectivo' => $apertura->movimientos->where('forma_pago', 'efectivo')->sum('monto'),
                'tarjeta_debito' => $apertura->movimientos->where('forma_pago', 'tarjeta_debito')->sum('monto'),
                'tarjeta_credito' => $apertura->movimientos->where('forma_pago', 'tarjeta_credito')->sum('monto'),
                'vale' => $apertura->movimientos->where('forma_pago', 'vale')->sum('monto'),
                'transferencia' => $apertura->movimientos->where('forma_pago', 'transferencia')->sum('monto'),
                'cheque' => $apertura->movimientos->where('forma_pago', 'cheque')->sum('monto'),
            ],
            'por_categoria' => [
                'ventas' => $apertura->movimientos->where('categoria', 'venta')->sum('monto'),
                'gastos' => $apertura->movimientos->where('categoria', 'gasto')->sum('monto'),
                'transferencias' => $apertura->movimientos->where('categoria', 'transferencia')->sum('monto'),
                'ajustes' => $apertura->movimientos->where('categoria', 'ajuste')->sum('monto'),
            ]
        ];
        
        return $resumen;
    }
}