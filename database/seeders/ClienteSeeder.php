<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        // ===== CLIENTES EMPRESA DEMO (empresa_id=1) =====

        // Clientes Contado
        Cliente::create([
            'empresa_id' => 1,
            'sucursal_id' => 1, // Matriz
            'nombre' => 'Juan Pérez López',
            'rfc' => 'PELJ850101ABC',
            'telefono' => '555-111-2222',
            'correo' => 'juan.perez@email.com',
            'direccion' => 'Calle Hidalgo #123, Col. Centro',
            'tipo' => 'contado',
            'limite_credito' => 0,
            'dias_credito' => 0,
            'activo' => true,
        ]);

        Cliente::create([
            'empresa_id' => 1,
            'sucursal_id' => 1, // Matriz
            'nombre' => 'María García Hernández',
            'rfc' => 'GAHM900202XYZ',
            'telefono' => '555-333-4444',
            'correo' => 'maria.garcia@email.com',
            'direccion' => 'Av. Juárez #456, Col. Reforma',
            'tipo' => 'contado',
            'limite_credito' => 0,
            'dias_credito' => 0,
            'activo' => true,
        ]);

        // Clientes Crédito
        Cliente::create([
            'empresa_id' => 1,
            'sucursal_id' => 1, // Matriz
            'nombre' => 'Distribuidora del Norte SA',
            'rfc' => 'DNO920515LMN',
            'telefono' => '555-555-6666',
            'correo' => 'ventas@distribuidoranorte.com',
            'direccion' => 'Blvd. Industrial #789, Col. Parque Industrial',
            'tipo' => 'credito',
            'limite_credito' => 50000.00,
            'dias_credito' => 30,
            'activo' => true,
        ]);

        Cliente::create([
            'empresa_id' => 1,
            'sucursal_id' => 2, // Sucursal Norte
            'nombre' => 'Comercializadora Express',
            'rfc' => 'CEX880707QRS',
            'telefono' => '555-777-8888',
            'correo' => 'compras@comexpress.com',
            'direccion' => 'Calle Comercio #321, Col. Mercado',
            'tipo' => 'credito',
            'limite_credito' => 25000.00,
            'dias_credito' => 15,
            'activo' => true,
        ]);

        // Cliente Público en General (sin RFC)
        Cliente::create([
            'empresa_id' => 1,
            'sucursal_id' => null, // Todas las sucursales
            'nombre' => 'Público en General',
            'rfc' => 'XAXX010101000',
            'telefono' => null,
            'correo' => null,
            'direccion' => null,
            'tipo' => 'contado',
            'limite_credito' => 0,
            'dias_credito' => 0,
            'activo' => true,
        ]);

        // Cliente inactivo
        Cliente::create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'nombre' => 'Tienda La Barata (Inactivo)',
            'rfc' => 'TIB800101XXX',
            'telefono' => '555-999-0000',
            'correo' => 'info@labarata.com',
            'direccion' => 'Av. Siempre Viva #742',
            'tipo' => 'credito',
            'limite_credito' => 10000.00,
            'dias_credito' => 30,
            'activo' => false,
        ]);

        // ===== CLIENTES TIENDA EXPRESS (empresa_id=2) =====
        Cliente::create([
            'empresa_id' => 2,
            'sucursal_id' => 4, // Tienda Express Centro
            'nombre' => 'Cliente Express 1',
            'rfc' => null,
            'telefono' => '555-444-3333',
            'correo' => 'cliente1@express.com',
            'direccion' => 'Calle Principal #1',
            'tipo' => 'contado',
            'limite_credito' => 0,
            'dias_credito' => 0,
            'activo' => true,
        ]);

        Cliente::create([
            'empresa_id' => 2,
            'sucursal_id' => 4,
            'nombre' => 'Super Tienda SA',
            'rfc' => 'STI950101ABC',
            'telefono' => '555-222-1111',
            'correo' => 'compras@supertienda.com',
            'direccion' => 'Av. Central #500',
            'tipo' => 'credito',
            'limite_credito' => 75000.00,
            'dias_credito' => 45,
            'activo' => true,
        ]);
    }
}