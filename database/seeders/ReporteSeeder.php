<?php
// database/seeders/ReporteSeeder.php

namespace Database\Seeders;

use App\Models\CajaMovimiento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReporteSeeder extends Seeder
{
    public function run(): void
    {
        // Crear vistas materializadas o resúmenes mensuales
        $months = [
            now()->subMonths(2)->format('Y-m'),
            now()->subMonths(1)->format('Y-m'),
            now()->format('Y-m'),
        ];
        
        foreach ($months as $month) {
            $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
            $endDate = \Carbon\Carbon::parse($month)->endOfMonth();
            
            $resumen = CajaMovimiento::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as total_ingresos'),
                    DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as total_egresos'),
                    DB::raw('COUNT(CASE WHEN tipo = "ingreso" THEN 1 END) as total_ventas')
                )
                ->first();
            
        }
    }
}