<?php

namespace Database\Seeders;

use App\Models\Licencia;
use Illuminate\Database\Seeder;

class LicenciaSeeder extends Seeder
{
    public function run(): void
    {
        $licencias = [
            [
                'nombre' => '1 Día',
                'dias' => 1,
                'max_usuarios' => 1,
                'max_sucursales' => 0,
                'precio' => 50.00,
                'activo' => true,
            ],
            [
                'nombre' => '7 Días',
                'dias' => 7,
                'max_usuarios' => 2,
                'max_sucursales' => 1,
                'precio' => 200.00,
                'activo' => true,
            ],
            [
                'nombre' => '15 Días',
                'dias' => 15,
                'max_usuarios' => 3,
                'max_sucursales' => 1,
                'precio' => 350.00,
                'activo' => true,
            ],
            [
                'nombre' => '1 Mes',
                'dias' => 30,
                'max_usuarios' => 5,
                'max_sucursales' => 2,
                'precio' => 500.00,
                'activo' => true,
            ],
            [
                'nombre' => '2 Meses',
                'dias' => 60,
                'max_usuarios' => 5,
                'max_sucursales' => 2,
                'precio' => 900.00,
                'activo' => true,
            ],
            [
                'nombre' => '3 Meses',
                'dias' => 90,
                'max_usuarios' => 10,
                'max_sucursales' => 3,
                'precio' => 1200.00,
                'activo' => true,
            ],
            [
                'nombre' => '6 Meses',
                'dias' => 180,
                'max_usuarios' => 20,
                'max_sucursales' => 5,
                'precio' => 2000.00,
                'activo' => true,
            ],
            [
                'nombre' => '1 Año',
                'dias' => 365,
                'max_usuarios' => 50,
                'max_sucursales' => 10,
                'precio' => 3500.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Permanente',
                'dias' => 99999,
                'max_usuarios' => 999,
                'max_sucursales' => 999,
                'precio' => 0.00,
                'activo' => true,
            ],
        ];

        foreach ($licencias as $licencia) {
            Licencia::create($licencia);
        }
    }
}