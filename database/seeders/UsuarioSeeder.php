<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'name' => 'Super Admin',
            'email' => 'yeahme200120@gmail.com',
            'password' => Hash::make('yesy2001'),
            'activo' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        // Administrador
        $admin = User::create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'name' => 'Administrador',
            'email' => 'admin@empresa.com',
            'password' => Hash::make('yesy2001'),
            'activo' => true,
        ]);
        $admin->assignRole('Administrador');

        // Vendedor Matriz
        $vendedor1 = User::create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'name' => 'Vendedor Matriz',
            'email' => 'vendedor1@empresa.com',
            'password' => Hash::make('yesy2001'),
            'activo' => true,
        ]);
        $vendedor1->assignRole('Vendedor');

        // Vendedor Norte
        $vendedor2 = User::create([
            'empresa_id' => 1,
            'sucursal_id' => 2,
            'name' => 'Vendedor Norte',
            'email' => 'vendedor2@empresa.com',
            'password' => Hash::make('yesy2001'),
            'activo' => true,
        ]);
        $vendedor2->assignRole('Vendedor');

        // Cajero Matriz
        $cajero1 = User::create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'name' => 'Cajero Matriz',
            'email' => 'cajero1@empresa.com',
            'password' => Hash::make('yesy2001'),
            'activo' => true,
        ]);
        $cajero1->assignRole('Cajero');

        // Cajero Norte
        $cajero2 = User::create([
            'empresa_id' => 1,
            'sucursal_id' => 2,
            'name' => 'Cajero Norte',
            'email' => 'cajero2@empresa.com',
            'password' => Hash::make('yesy2001'),
            'activo' => true,
        ]);
        $cajero2->assignRole('Cajero');

        // Cobrador
        $cobrador = User::create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'name' => 'Cobrador',
            'email' => 'cobrador@empresa.com',
            'password' => Hash::make('yesy2001'),
            'activo' => true,
        ]);
        $cobrador->assignRole('Cobrador');

        // Usuario inactivo para pruebas
        $inactivo = User::create([
            'empresa_id' => 1,
            'sucursal_id' => null,
            'name' => 'Usuario Inactivo',
            'email' => 'inactivo@empresa.com',
            'password' => Hash::make('yesy2001'),
            'activo' => false,
        ]);
        $inactivo->assignRole('Vendedor');
    }
}