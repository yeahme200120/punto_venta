<?php
// database/seeders/ContrasenaMaestraSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ContrasenaMaestra;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ContrasenaMaestraSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar al Super Admin existente (el que ya tienes creado)
        $superAdmin = User::where('email', 'yeahme200120@gmail.com')->first();
        
        if (!$superAdmin) {
            // Si no existe, crear uno nuevo
            $superAdmin = User::firstOrCreate(
                ['email' => 'yeahme200120@gmail.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password123'),
                    'empresa_id' => 1,
                    'sucursal_id' => 1,
                ]
            );
            
            // Asignar rol Super Admin
            $superAdmin->assignRole('Super Admin');
        }

        // Crear o actualizar contraseña maestra para Super Admin
        ContrasenaMaestra::updateOrCreate(
            [
                'user_id' => $superAdmin->id,
                'tipo' => 'super_admin'
            ],
            [
                'password_hash' => Hash::make('master123'),
                'password_texto' => 'master123',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // También crear para el admin@miempresa.com si existe
        $admin = User::where('email', 'admin@miempresa.com')->first();
        if ($admin) {
            ContrasenaMaestra::updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'tipo' => 'admin'
                ],
                [
                    'password_hash' => Hash::make('admin123'),
                    'password_texto' => 'admin123',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        $this->command->info('Contraseña maestra configurada correctamente');
        $this->command->info('Super Admin: yeahme200120@gmail.com');
        $this->command->info('Contraseña maestra: master123');
    }
}