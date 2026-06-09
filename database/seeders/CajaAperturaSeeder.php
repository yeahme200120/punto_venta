<?php
// database/seeders/CajaAperturaSeeder.php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\User;
use App\Models\CajaApertura;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class CajaAperturaSeeder extends Seeder
{
    public function run(): void
    {
        $cajas = Caja::where('activo', true)->get();
        $usuarios = User::all();
        
        // Generar fechas: desde hace 2 meses hasta hoy
        $startDate = now()->subMonths(2)->startOfDay();
        $endDate = now()->endOfDay();
        
        $currentDate = clone $startDate;
        $aperturas = [];
        
        while ($currentDate <= $endDate) {
            // Solo días hábiles (lunes a viernes)
            if ($currentDate->isWeekday()) {
                foreach ($cajas as $caja) {
                    // Obtener empresa_id desde la sucursal de la caja
                    $sucursal = Sucursal::find($caja->sucursal_id);
                    $empresaId = $sucursal ? $sucursal->empresa_id : null;
                    
                    if (!$empresaId) {
                        continue; // Saltar si no hay empresa asignada
                    }
                    
                    // Monto inicial aleatorio entre 1000 y 8000
                    $montoInicial = rand(1000, 8000);
                    
                    $fechaApertura = $currentDate->copy()->setTime(rand(8, 10), rand(0, 59));
                    $fechaCierre = $currentDate->copy()->setTime(rand(18, 21), rand(0, 59));
                    $esHoy = $currentDate->isToday();
                    
                    $aperturas[] = [
                        'empresa_id' => $empresaId, // ← CAMPO AGREGADO
                        'caja_id' => $caja->id,
                        'user_id' => $usuarios->random()->id,
                        'sucursal_id' => $caja->sucursal_id,
                        'fecha' => $currentDate->format('Y-m-d'),
                        'fecha_apertura' => $fechaApertura,
                        'fecha_cierre' => $esHoy ? null : $fechaCierre,
                        'monto_inicial' => $montoInicial,
                        'monto_final' => null,
                        'total_ingresos' => 0,
                        'total_egresos' => 0,
                        'estado' => $esHoy ? 'abierta' : 'cerrada',
                        'observaciones_apertura' => 'Apertura automática - Turno ' . (rand(1, 2) == 1 ? 'Mañana' : 'Tarde'),
                        'observaciones_cierre' => $esHoy ? null : 'Cierre automático del día',
                        'created_at' => $fechaApertura,
                        'updated_at' => $esHoy ? $fechaApertura : $fechaCierre,
                    ];
                }
            }
            $currentDate->addDay();
        }
        
        // Insertar en batches para mejor rendimiento
        $chunks = array_chunk($aperturas, 50);
        foreach ($chunks as $chunk) {
            CajaApertura::insert($chunk);
        }
    }
}