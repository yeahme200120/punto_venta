<?php
// database/seeders/CajaSeeder.php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = Sucursal::all();
        
        foreach ($sucursales as $sucursal) {
            // Caja principal por sucursal
            Caja::create([
                'empresa_id' => $sucursal->empresa_id,
                'sucursal_id' => $sucursal->id,
                'nombre' => 'Caja Principal',
                'codigo' => Caja::generarCodigo(), // Usa el método corregido
                'descripcion' => 'Caja principal de la sucursal',
                'saldo_inicial' => 5000.00,
                'saldo_actual' => 5000.00,
                'activo' => true,
                'permite_multiple' => false,
            ]);
            
            // Caja secundaria
            Caja::create([
                'empresa_id' => $sucursal->empresa_id,
                'sucursal_id' => $sucursal->id,
                'nombre' => 'Caja Secundaria',
                'codigo' => Caja::generarCodigo(), // Usa el método corregido
                'descripcion' => 'Caja secundaria para alta demanda',
                'saldo_inicial' => 3000.00,
                'saldo_actual' => 3000.00,
                'activo' => true,
                'permite_multiple' => true,
            ]);
        }
    }
}