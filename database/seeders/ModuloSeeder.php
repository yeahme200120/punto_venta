<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            ['nombre' => 'Dashboard',       'icono' => '📊', 'orden' => 1,  'activo' => true],
            ['nombre' => 'Empresas',        'icono' => '🏢', 'orden' => 2,  'activo' => true],
            ['nombre' => 'Licencias',       'icono' => '📜', 'orden' => 3,  'activo' => true],
            ['nombre' => 'Inventario',      'icono' => '📦', 'orden' => 4,  'activo' => true],
            ['nombre' => 'Compras',         'icono' => '🛒', 'orden' => 5,  'activo' => false],
            ['nombre' => 'Proveedores',     'icono' => '🚚', 'orden' => 6,  'activo' => true],
            ['nombre' => 'Ventas',          'icono' => '💰', 'orden' => 7,  'activo' => true],
            ['nombre' => 'Facturacion',     'icono' => '🧾', 'orden' => 8,  'activo' => false],
            ['nombre' => 'Clientes',        'icono' => '👥', 'orden' => 9,  'activo' => true],
            ['nombre' => 'Caja',            'icono' => '💵', 'orden' => 10, 'activo' => true],
            ['nombre' => 'Cobranza',        'icono' => '📋', 'orden' => 11, 'activo' => true],
            ['nombre' => 'FormasPago',      'icono' => '💳', 'orden' => 12, 'activo' => true],
            ['nombre' => 'Notificaciones',  'icono' => '🔔', 'orden' => 13, 'activo' => false],
            ['nombre' => 'Impresoras',      'icono' => '🖨️', 'orden' => 14, 'activo' => false],
            ['nombre' => 'Ticket',          'icono' => '🎫', 'orden' => 15, 'activo' => true],
            ['nombre' => 'Usuarios',        'icono' => '🔐', 'orden' => 16, 'activo' => true],
            ['nombre' => 'Reportes',        'icono' => '📈', 'orden' => 17, 'activo' => true],
            ['nombre' => 'Respaldos',       'icono' => '💾', 'orden' => 18, 'activo' => true],
        ];

        foreach ($modulos as $modulo) {
            Modulo::create($modulo);
        }
    }
}