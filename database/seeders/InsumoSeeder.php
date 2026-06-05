<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class InsumoSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = \App\Models\Empresa::all();

        if ($empresas->isEmpty()) {
            $this->command->warn('No hay empresas, ejecuta EmpresaSeeder primero');
            return;
        }

        foreach ($empresas as $empresa) {
            $proveedores = Proveedor::where('empresa_id', $empresa->id)->get();

            if ($proveedores->isEmpty()) {
                $this->command->warn("No hay proveedores para empresa {$empresa->id}, saltando...");
                continue;
            }

            $insumos = [
                [
                    'codigo' => 'INS-001',
                    'nombre' => 'Harina de trigo',
                    'descripcion' => 'Harina de trigo premium',
                    'unidad_medida' => 'kg',
                    'costo_unitario' => 25.50,
                    'stock_minimo' => 50,
                    'stock_maximo' => 500,
                ],
                [
                    'codigo' => 'INS-002',
                    'nombre' => 'Aceite vegetal',
                    'descripcion' => 'Aceite de cocina vegetal',
                    'unidad_medida' => 'litro',
                    'costo_unitario' => 35.00,
                    'stock_minimo' => 30,
                    'stock_maximo' => 300,
                ],
                [
                    'codigo' => 'INS-003',
                    'nombre' => 'Tela de algodón',
                    'descripcion' => 'Tela 100% algodón',
                    'unidad_medida' => 'metro',
                    'costo_unitario' => 45.00,
                    'stock_minimo' => 100,
                    'stock_maximo' => 1000,
                ],
                [
                    'codigo' => 'INS-004',
                    'nombre' => 'Resistencia electrónica',
                    'descripcion' => 'Resistencia 10k ohm',
                    'unidad_medida' => 'pieza',
                    'costo_unitario' => 0.50,
                    'stock_minimo' => 500,
                    'stock_maximo' => 5000,
                ],
                [
                    'codigo' => 'INS-005',
                    'nombre' => 'Madera de pino',
                    'descripcion' => 'Madera tratada',
                    'unidad_medida' => 'pieza',
                    'costo_unitario' => 120.00,
                    'stock_minimo' => 50,
                    'stock_maximo' => 500,
                ],
            ];

            foreach ($insumos as $index => $insumo) {
                $proveedor = $proveedores[$index % count($proveedores)];

                Insumo::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'codigo' => $insumo['codigo']
                    ],
                    [
                        'proveedor_id' => $proveedor->id,
                        'nombre' => $insumo['nombre'],
                        'descripcion' => $insumo['descripcion'],
                        'unidad_medida' => $insumo['unidad_medida'],
                        'costo_unitario' => $insumo['costo_unitario'],
                        'stock' => rand(100, 500),
                        'stock_minimo' => $insumo['stock_minimo'],
                        'stock_maximo' => $insumo['stock_maximo'],
                        'activo' => true,
                    ]
                );
            }
            
            $this->command->info("Insumos creados para empresa: {$empresa->nombre}");
        }
    }
}