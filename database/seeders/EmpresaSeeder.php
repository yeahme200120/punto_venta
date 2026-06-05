<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        // Empresa Demo con licencia Permanente (id=9)
        $empresa1 = Empresa::create([
            'licencia_id' => 9, // Permanente
            'nombre' => 'Empresa Demo',
            'rfc' => 'XAXX010101000',
            'direccion' => 'Av. Principal #123, Col. Centro',
            'telefono' => '555-123-4567',
            'correo' => 'contacto@empresademo.com',
            'logo' => null,
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addYears(100),
            'activo' => true,
        ]);

        // Sucursales de Empresa Demo
        Sucursal::create([
            'empresa_id' => $empresa1->id,
            'nombre' => 'Matriz',
            'direccion' => 'Av. Principal #123, Col. Centro',
            'telefono' => '555-123-4567',
            'activo' => true,
        ]);

        Sucursal::create([
            'empresa_id' => $empresa1->id,
            'nombre' => 'Sucursal Norte',
            'direccion' => 'Blvd. Norte #456, Col. Industrial',
            'telefono' => '555-987-6543',
            'activo' => true,
        ]);

        Sucursal::create([
            'empresa_id' => $empresa1->id,
            'nombre' => 'Sucursal Sur',
            'direccion' => 'Calle Sur #789, Col. Reforma',
            'telefono' => '555-111-2222',
            'activo' => false,
        ]);

        // Empresa con licencia de 1 Mes (id=4)
        $empresa2 = Empresa::create([
            'licencia_id' => 4, // 1 Mes
            'nombre' => 'Tienda Express',
            'rfc' => 'TEX123456789',
            'direccion' => 'Calle Comercio #45',
            'telefono' => '555-333-4444',
            'correo' => 'info@tiendaexpress.com',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonth(),
            'activo' => true,
        ]);

        Sucursal::create([
            'empresa_id' => $empresa2->id,
            'nombre' => 'Tienda Express Centro',
            'direccion' => 'Calle Comercio #45',
            'telefono' => '555-333-4444',
            'activo' => true,
        ]);
    }
}