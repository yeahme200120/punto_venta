<?php
// database/seeders/CajaAperturaSeeder.php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\User;
use App\Models\CajaApertura;
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
                    // Monto inicial aleatorio entre 1000 y 8000
                    $montoInicial = rand(1000, 8000);
                    
                    $aperturas[] = [
                        'caja_id' => $caja->id,
                        'user_id' => $usuarios->random()->id,
                        'sucursal_id' => $caja->sucursal_id,
                        'fecha' => $currentDate->format('Y-m-d'),
                        'fecha_apertura' => $currentDate->copy()->setTime(rand(8, 10), rand(0, 59))->format('Y-m-d H:i:s'),
                        'fecha_cierre' => $currentDate->copy()->setTime(rand(18, 21), rand(0, 59))->format('Y-m-d H:i:s'),
                        'monto_inicial' => $montoInicial,
                        'monto_final' => null, // Se actualizará después
                        'total_ingresos' => 0, // Se actualizará después
                        'total_egresos' => 0, // Se actualizará después
                        'estado' => $currentDate->isToday() ? 'abierta' : 'cerrada',
                        'observaciones_apertura' => 'Apertura automática - Turno ' . (rand(1, 2) == 1 ? 'Mañana' : 'Tarde'),
                        'observaciones_cierre' => $currentDate->isToday() ? null : 'Cierre automático del día',
                        'created_at' => $currentDate->copy()->setTime(rand(8, 10), rand(0, 59)),
                        'updated_at' => $currentDate->copy()->setTime(rand(18, 21), rand(0, 59)),
                    ];
                }
            }
            $currentDate->addDay();
        }
        
        foreach ($aperturas as $apertura) {
            CajaApertura::create($apertura);
        }
    }
}