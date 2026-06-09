<?php
// app/Services/CajaService.php
namespace App\Services;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Models\CajaTransferencia;
use App\Models\ContrasenaMaestra;
use App\Models\User;
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


    public static function abrirCaja($cajaId, $userId, $sucursalId, $empresaId, $montoInicial, $observaciones = null)
    {
        $user = User::find($userId);
        if (!$user)
            throw new \Exception('Usuario no encontrado.');

        $caja = Caja::find($cajaId);
        if (!$caja)
            throw new \Exception('Caja no encontrada.');

        // Verificar rol del usuario
        $esAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');

        // 🔥 1. Verificar si la caja permite múltiples aperturas (SOLO para NO admins)
        if (!$caja->permite_multiple && !$esAdmin) {
            $aperturaExistente = CajaApertura::where('caja_id', $cajaId)
                ->where('empresa_id', $empresaId)
                ->where('estado', 'abierta')
                ->first();

            if ($aperturaExistente) {
                throw new \Exception("La caja '{$caja->nombre}' ya tiene una apertura activa y no permite múltiples aperturas simultáneas.");
            }
        }

        // 🔥 2. Validar que el usuario no tenga otra caja abierta (SOLO para NO admins)
        // Los admins pueden tener múltiples cajas abiertas
        if (!$esAdmin) {
            $aperturaUsuario = CajaApertura::where('user_id', $userId)
                ->where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', 'abierta')
                ->first();

            if ($aperturaUsuario) {
                throw new \Exception("Ya tienes una caja abierta en esta sucursal (Caja: {$aperturaUsuario->caja->nombre}). Debes cerrarla antes de abrir otra.");
            }
        }

        // 🔥 3. Para admins, solo verificar que no estén abriendo la MISMA caja dos veces
        if ($esAdmin) {
            $mismaCaja = CajaApertura::where('caja_id', $cajaId)
                ->where('user_id', $userId)
                ->where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', 'abierta')
                ->first();

            if ($mismaCaja) {
                throw new \Exception("Ya tienes esta caja abierta. No puedes abrir la misma caja dos veces.");
            }
        }

        // Crear la apertura
        $apertura = CajaApertura::create([
            'empresa_id' => $empresaId,
            'caja_id' => $cajaId,
            'user_id' => $userId,
            'sucursal_id' => $sucursalId,
            'fecha' => now()->toDateString(),
            'fecha_apertura' => now(),
            'monto_inicial' => $montoInicial,
            'monto_final' => null,
            'total_ingresos' => 0,
            'total_egresos' => 0,
            'estado' => 'abierta',
            'observaciones_apertura' => $observaciones,
        ]);

        $caja->increment('saldo_actual', $montoInicial);

        return $apertura;
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
            $passwordMaestraDB = ContrasenaMaestra::where('user_id', $autorizadorId)
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
            $passwordMaestraDB = ContrasenaMaestra::where('user_id', $autorizadorId)
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


    public static function resumenDia($aperturaId)
    {
        $apertura = CajaApertura::with('movimientos')->findOrFail($aperturaId);

        // Asegurar que todos los valores sean float
        $montoInicial = floatval($apertura->monto_inicial ?? 0);
        $totalIngresos = floatval($apertura->total_ingresos ?? 0);
        $totalEgresos = floatval($apertura->total_egresos ?? 0);

        // Calcular por forma de pago
        $porFormaPago = [];
        foreach ($apertura->movimientos->where('tipo', 'ingreso') as $movimiento) {
            $forma = $movimiento->forma_pago;
            if (!isset($porFormaPago[$forma])) {
                $porFormaPago[$forma] = 0;
            }
            $porFormaPago[$forma] += floatval($movimiento->monto);
        }

        // Calcular promedios
        $totalTransacciones = $apertura->movimientos->count();
        $totalClientes = $apertura->movimientos->whereNotNull('cliente_id')->groupBy('cliente_id')->count();

        $resumen = [
            'fecha' => $apertura->fecha ? $apertura->fecha->format('d/m/Y') : now()->format('d/m/Y'),
            'apertura' => $montoInicial,
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'saldo_esperado' => $montoInicial + $totalIngresos - $totalEgresos,
            'por_forma_pago' => $porFormaPago,
            'promedio_venta' => $totalTransacciones > 0 ? $totalIngresos / $totalTransacciones : 0,
            'promedio_ingreso' => $apertura->movimientos->where('tipo', 'ingreso')->count() > 0
                ? $totalIngresos / $apertura->movimientos->where('tipo', 'ingreso')->count()
                : 0,
            'total_transacciones' => $totalTransacciones,
            'total_clientes' => $totalClientes,
        ];

        return $resumen;
    }
}