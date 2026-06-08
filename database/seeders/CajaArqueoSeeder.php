<?php
// database/seeders/CajaArqueoSeeder.php

namespace Database\Seeders;

use App\Models\CajaApertura;
use App\Models\CajaArqueo;
use App\Models\User;
use Illuminate\Database\Seeder;

class CajaArqueoSeeder extends Seeder
{
    public function run(): void
    {
        $aperturas = CajaApertura::where('estado', 'cerrada')->get();
        $usuarios = User::all();
        
        foreach ($aperturas as $apertura) {
            // Solo algunas aperturas tienen arqueo (60% del tiempo)
            if (rand(1, 100) <= 60) {
                // Obtener movimientos agrupados por forma de pago
                $movimientos = $apertura->movimientos;
                
                $totalesSistema = [
                    'efectivo' => $movimientos->where('forma_pago', 'efectivo')->sum('monto'),
                    'tarjeta_debito' => $movimientos->where('forma_pago', 'tarjeta_debito')->sum('monto'),
                    'tarjeta_credito' => $movimientos->where('forma_pago', 'tarjeta_credito')->sum('monto'),
                    'transferencia' => $movimientos->where('forma_pago', 'transferencia')->sum('monto'),
                ];
                
                // Simular pequeñas diferencias (±2%)
                $diferenciaPorcentaje = rand(-2, 2) / 100;
                
                $arqueo = [
                    'caja_apertura_id' => $apertura->id,
                    'user_id' => $usuarios->random()->id,
                    'sucursal_id' => $apertura->sucursal_id,
                    'fecha_arqueo' => $apertura->fecha_cierre,
                    'efectivo_contado' => round($totalesSistema['efectivo'] * (1 + $diferenciaPorcentaje), 2),
                    'tarjeta_debito_contado' => round($totalesSistema['tarjeta_debito'] * (1 + $diferenciaPorcentaje), 2),
                    'tarjeta_credito_contado' => round($totalesSistema['tarjeta_credito'] * (1 + $diferenciaPorcentaje), 2),
                    'transferencia_contado' => round($totalesSistema['transferencia'] * (1 + $diferenciaPorcentaje), 2),
                    'vale_contado' => 0,
                    'cheque_contado' => 0,
                    'observaciones' => $diferenciaPorcentaje != 0 ? 'Pequeña diferencia encontrada en el conteo' : 'Conteo exacto',
                    'estado' => 'finalizado',
                    'created_at' => $apertura->fecha_cierre,
                    'updated_at' => $apertura->fecha_cierre,
                ];
                
                // Calcular totales
                $totalContado = $arqueo['efectivo_contado'] + $arqueo['tarjeta_debito_contado'] + 
                                $arqueo['tarjeta_credito_contado'] + $arqueo['transferencia_contado'];
                
                $totalSistema = $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos;
                
                $arqueo['total_contado'] = $totalContado;
                $arqueo['total_sistema'] = $totalSistema;
                $arqueo['diferencia'] = $totalContado - $totalSistema;
                
                CajaArqueo::create($arqueo);
            }
        }
    }
}