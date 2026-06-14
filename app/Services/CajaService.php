<?php
// app/Services/CajaService.php
namespace App\Services;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Models\CajaTransferencia;
use App\Models\ContrasenaMaestra;
use App\Models\FormaPago;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

            // 🔥 SOLO actualizar totales SI NO requiere autorización
            if (!$requiereAutorizacion) {
                if ($data['tipo'] === 'ingreso') {
                    $apertura->increment('total_ingresos', $data['monto']);
                    $apertura->caja->increment('saldo_actual', $data['monto']);
                } else {
                    $apertura->increment('total_egresos', $data['monto']);
                    $apertura->caja->decrement('saldo_actual', $data['monto']);
                }
            }

            DB::commit();
            return $movimiento;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // app/Services/CajaService.php

    public static function autorizarMovimiento($movimientoId, $passwordMaestra)
    {
        DB::beginTransaction();
        try {
            $movimiento = CajaMovimiento::findOrFail($movimientoId);

            if (!$movimiento->requiere_autorizacion) {
                throw new \Exception('Este movimiento no requiere autorización.');
            }

            if ($movimiento->autorizado_por) {
                throw new \Exception('Este movimiento ya fue autorizado.');
            }

            // 🔥 Validar la contraseña de forma GLOBAL (identificar quién la ingresó)
            $autorizador = ContrasenaMaestra::validarPasswordGlobal($passwordMaestra, $movimiento->cajaApertura->empresa_id);

            if (!$autorizador) {
                throw new \Exception('Contraseña maestra incorrecta o no tienes autorización para esta operación.');
            }

            // 🔥 Verificar que el autorizador tenga permisos (Super Admin o Administrador)
            if (!$autorizador->hasRole('Super Admin') && !$autorizador->hasRole('Administrador')) {
                throw new \Exception('No tienes permisos para autorizar movimientos. Solo Super Admin o Administradores pueden autorizar.');
            }

            // 🔥 Actualizar movimiento
            $movimiento->update([
                'requiere_autorizacion' => false,
                'autorizado_por' => $autorizador->id,
                'autorizado_en' => now()
            ]);

            // 🔥 ACTUALIZAR TOTALES DE LA CAJA al autorizar
            $apertura = $movimiento->cajaApertura;

            if ($movimiento->tipo === 'ingreso') {
                $apertura->increment('total_ingresos', $movimiento->monto);
                $apertura->caja->increment('saldo_actual', $movimiento->monto);
            } else {
                $apertura->increment('total_egresos', $movimiento->monto);
                $apertura->caja->decrement('saldo_actual', $movimiento->monto);
            }

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
    public static function transferirEntreCajas($origenId, $destinoId, $userId, $monto, $motivo, $formaPago = 'efectivo')
    {
        DB::beginTransaction();
        try {
            // Verificar que la forma de pago exista en el catálogo
            $formaPagoExistente = FormaPago::where('clave', $formaPago)->where('activo_global', true)->first();
            if (!$formaPagoExistente) {
                throw new \Exception("La forma de pago '{$formaPago}' no es válida o está desactivada.");
            }
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
                'forma_pago' => $formaPago,
                'estado' => 'pendiente'
            ]);

            DB::commit();
            return $transferencia;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function aprobarTransferencia($transferenciaId, $passwordMaestra)
    {
        DB::beginTransaction();
        try {
            $transferencia = CajaTransferencia::findOrFail($transferenciaId);

            if ($transferencia->estado !== 'pendiente') {
                throw new \Exception('Esta transferencia ya fue procesada o cancelada.');
            }

            // 🔥 Validar la contraseña de forma GLOBAL (identificar quién la ingresó)
            // Obtener la empresa de la caja origen
            $cajaOrigen = Caja::find($transferencia->caja_origen_id);
            $empresaId = $cajaOrigen ? $cajaOrigen->empresa_id : null;

            $autorizador = ContrasenaMaestra::validarPasswordGlobal($passwordMaestra, $empresaId);

            if (!$autorizador) {
                throw new \Exception('Contraseña maestra incorrecta o no tienes autorización para esta operación.');
            }

            // 🔥 Verificar que el autorizador tenga permisos (Super Admin o Administrador)
            if (!$autorizador->hasRole('Super Admin') && !$autorizador->hasRole('Administrador')) {
                throw new \Exception('No tienes permisos para autorizar transferencias. Solo Super Admin o Administradores pueden autorizar.');
            }

            // Obtener las aperturas
            $aperturaOrigen = CajaApertura::find($transferencia->caja_apertura_origen_id);
            $aperturaDestino = CajaApertura::find($transferencia->caja_apertura_destino_id);

            if (!$aperturaOrigen || !$aperturaDestino) {
                throw new \Exception('No se encontraron las aperturas de caja.');
            }

            // Verificar saldo en caja origen
            $saldoOrigen = $aperturaOrigen->saldoActual();
            if ($saldoOrigen < $transferencia->monto) {
                throw new \Exception("Saldo insuficiente en caja origen. Saldo actual: $" . number_format($saldoOrigen, 2));
            }

            // 🔥 Verificar que la forma de pago aún sea válida
            $formaPagoExistente = FormaPago::where('clave', $transferencia->forma_pago)->where('activo_global', true)->first();
            $formaPago = $formaPagoExistente ? $transferencia->forma_pago : 'efectivo';

            // Registrar egreso en caja origen
            CajaMovimiento::create([
                'caja_apertura_id' => $aperturaOrigen->id,
                'user_id' => $autorizador->id,
                'sucursal_id' => $aperturaOrigen->sucursal_id,
                'tipo' => 'egreso',
                'categoria' => 'transferencia',
                'forma_pago' => $formaPago,
                'monto' => $transferencia->monto,
                'concepto' => "Transferencia a caja {$transferencia->cajaDestino->codigo} - {$transferencia->motivo}",
                'referencia' => "TRANSF-{$transferencia->id}",
                'requiere_autorizacion' => false
            ]);

            // Registrar ingreso en caja destino
            CajaMovimiento::create([
                'caja_apertura_id' => $aperturaDestino->id,
                'user_id' => $autorizador->id,
                'sucursal_id' => $aperturaDestino->sucursal_id,
                'tipo' => 'ingreso',
                'categoria' => 'transferencia',
                'forma_pago' => $formaPago,
                'monto' => $transferencia->monto,
                'concepto' => "Transferencia desde caja {$transferencia->cajaOrigen->codigo} - {$transferencia->motivo}",
                'referencia' => "TRANSF-{$transferencia->id}",
                'requiere_autorizacion' => false
            ]);

            // Actualizar saldos
            $aperturaOrigen->increment('total_egresos', $transferencia->monto);
            $aperturaOrigen->caja->decrement('saldo_actual', $transferencia->monto);

            $aperturaDestino->increment('total_ingresos', $transferencia->monto);
            $aperturaDestino->caja->increment('saldo_actual', $transferencia->monto);

            // Actualizar transferencia
            $transferencia->update([
                'estado' => 'aprobada',
                'autorizado_por' => $autorizador->id,
                'autorizado_en' => now()
            ]);

            DB::commit();
            return $transferencia;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public static function resumenDia($aperturaId)
    {
        Log::info('=== CajaService::resumenDia ===', ['apertura_id' => $aperturaId]);

        $apertura = CajaApertura::findOrFail($aperturaId);

        Log::info('Apertura encontrada:', [
            'id' => $apertura->id,
            'monto_inicial' => $apertura->monto_inicial,
            'total_ingresos' => $apertura->total_ingresos,
            'total_egresos' => $apertura->total_egresos
        ]);

        // 🔥 Usar los totales ya almacenados en la apertura (que solo incluyen autorizados)
        $totalRetiros = CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'egreso')
            ->where('categoria', 'retiro_parcial')
            ->where('requiere_autorizacion', false)  // Solo autorizados
            ->sum('monto');

        // 🔥 También calcular pendientes de autorización
        $pendientesIngresos = CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'ingreso')
            ->where('requiere_autorizacion', true)
            ->whereNull('autorizado_por')
            ->sum('monto');
        $pendientesEgresos = CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'egreso')
            ->where('requiere_autorizacion', true)
            ->whereNull('autorizado_por')
            ->sum('monto');

        $resultado = [
            'fecha' => $apertura->fecha->format('d/m/Y'),
            'apertura' => (float) $apertura->monto_inicial,
            'total_ingresos' => (float) $apertura->total_ingresos,
            'total_egresos' => (float) $apertura->total_egresos,
            'total_retiros' => (float) $totalRetiros,
            'pendientes_ingresos' => (float) $pendientesIngresos,
            'pendientes_egresos' => (float) $pendientesEgresos,
            'saldo_esperado' => (float) ($apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos),
            'por_forma_pago' => self::calcularPorFormaPago($aperturaId)
        ];

        Log::info('Resultado resumen:', $resultado);

        return $resultado;
    }
    public static function calcularPorFormaPago($aperturaId)
    {
        $porFormaPago = [];

        $movimientos = CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'ingreso')
            ->where('requiere_autorizacion', false)  // Solo autorizados
            ->get();

        foreach ($movimientos as $movimiento) {
            $forma = $movimiento->forma_pago;
            if (!isset($porFormaPago[$forma])) {
                $porFormaPago[$forma] = 0;
            }
            $porFormaPago[$forma] += floatval($movimiento->monto);
        }

        return $porFormaPago;
    }
}