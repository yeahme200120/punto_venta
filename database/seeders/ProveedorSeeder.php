<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = \App\Models\Empresa::all();

        if ($empresas->isEmpty()) {
            $this->command->warn('No hay empresas, ejecuta EmpresaSeeder primero');
            return;
        }

        foreach ($empresas as $empresa) {
            $proveedores = [
                [
                    'nombre' => 'Distribuidora Electrónica S.A.',
                    'rfc' => 'DESA123456789',
                    'telefono' => '555-123-4567',
                    'correo' => 'ventas@distribuidoraelectronica.com',
                    'direccion' => 'Av. Tecnológico #123, Col. Industrial'
                ],
                [
                    'nombre' => 'Proveedores Unidos',
                    'rfc' => 'PROU987654321',
                    'telefono' => '555-765-4321',
                    'correo' => 'contacto@proveedoresunidos.com',
                    'direccion' => 'Calle Comercio #456, Centro'
                ],
                [
                    'nombre' => 'Insumos y Materiales S.A.',
                    'rfc' => 'IMSA456789123',
                    'telefono' => '555-987-6543',
                    'correo' => 'ventas@insumosymateriales.com',
                    'direccion' => 'Boulevard Industrial #789'
                ],
                [
                    'nombre' => 'Alimentos del Valle',
                    'rfc' => 'ALVA741852963',
                    'telefono' => '555-321-6547',
                    'correo' => 'pedidos@alimentosdelvalle.com',
                    'direccion' => 'Av. Principal #321'
                ],
                [
                    'nombre' => 'Textiles Mexicanos',
                    'rfc' => 'TEMX852963741',
                    'telefono' => '555-456-7891',
                    'correo' => 'ventas@textilesmexicanos.com',
                    'direccion' => 'Calle Textil #147'
                ],
            ];

            foreach ($proveedores as $proveedor) {
                Proveedor::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'rfc' => $proveedor['rfc']
                    ],
                    [
                        'nombre' => $proveedor['nombre'],
                        'telefono' => $proveedor['telefono'],
                        'correo' => $proveedor['correo'],
                        'direccion' => $proveedor['direccion'],
                        'activo' => true,
                    ]
                );
            }
            
            $this->command->info("Proveedores creados para empresa: {$empresa->nombre}");
        }
    }
}