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
        // ============================================================
        // 1. SUPER ADMIN
        // ============================================================
        $superAdmin = User::where('email', 'yeahme200120@gmail.com')->first();
        
        if (!$superAdmin) {
            // Buscar por rol Super Admin
            $superAdmin = User::role('Super Admin')->first();
        }
        
        if (!$superAdmin) {
            // Crear nuevo Super Admin
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
            $this->command->info('✅ Super Admin creado: yeahme200120@gmail.com');
        } else {
            $this->command->info('✅ Super Admin encontrado: ' . $superAdmin->email);
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
            ]
        );
        $this->command->info('🔑 Contraseña maestra para Super Admin: master123');

        // ============================================================
        // 2. ADMINISTRADOR - Buscar por múltiples criterios
        // ============================================================
        $admin = null;
        
        // Buscar por email específico
        $admin = User::where('email', 'admin@empresa.com')->first();
        
        if (!$admin) {
            // Buscar por email alternativo
            $admin = User::where('email', 'admin@miempresa.com')->first();
        }
        
        if (!$admin) {
            // Buscar por rol Administrador
            $admin = User::role('Administrador')->first();
        }
        
        if (!$admin) {
            // Buscar cualquier usuario con rol Admin
            $admin = User::role('Admin')->first();
        }
        
        if (!$admin) {
            // Buscar por ID común (2 suele ser el primer admin)
            $admin = User::find(2);
        }
        
        if (!$admin) {
            // Crear un nuevo Administrador si no existe ninguno
            $admin = User::firstOrCreate(
                ['email' => 'admin@empresa.com'],
                [
                    'name' => 'Administrador',
                    'password' => Hash::make('admin123'),
                    'empresa_id' => 1,
                    'sucursal_id' => 1,
                ]
            );
            
            // Asignar rol Administrador
            $admin->assignRole('Administrador');
            $this->command->info('✅ Administrador creado: admin@empresa.com');
        } else {
            $this->command->info('✅ Administrador encontrado: ' . $admin->email);
        }

        // Crear o actualizar contraseña maestra para Administrador
        ContrasenaMaestra::updateOrCreate(
            [
                'user_id' => $admin->id,
                'tipo' => 'admin'
            ],
            [
                'password_hash' => Hash::make('admin123'),
                'password_texto' => 'admin123',
                'activo' => true,
            ]
        );
        $this->command->info('🔑 Contraseña maestra para Administrador: admin123');

        // ============================================================
        // 3. ADMINISTRADOR ADICIONAL (por si hay más de uno)
        // ============================================================
        $adminsAdicionales = User::role('Administrador')->where('id', '!=', $admin->id)->get();
        
        foreach ($adminsAdicionales as $adminAdicional) {
            ContrasenaMaestra::updateOrCreate(
                [
                    'user_id' => $adminAdicional->id,
                    'tipo' => 'admin'
                ],
                [
                    'password_hash' => Hash::make('admin123'),
                    'password_texto' => 'admin123',
                    'activo' => true,
                ]
            );
            $this->command->info("🔑 Contraseña maestra para Administrador adicional ({$adminAdicional->email}): admin123");
        }

        // ============================================================
        // 4. OPCIONAL: También para el usuario actual (autenticado)
        // ============================================================
        // Esto es útil cuando ejecutas el seeder y quieres que el usuario
        // con el que estás logueado también tenga su contraseña maestra
        
        // Verificar si estamos en entorno de consola y hay un usuario autenticado
        if (auth()->check()) {
            $currentUser = auth()->user();
            $tipo = $currentUser->hasRole('Super Admin') ? 'super_admin' : 'admin';
            
            ContrasenaMaestra::updateOrCreate(
                [
                    'user_id' => $currentUser->id,
                    'tipo' => $tipo
                ],
                [
                    'password_hash' => Hash::make($tipo === 'super_admin' ? 'master123' : 'admin123'),
                    'password_texto' => $tipo === 'super_admin' ? 'master123' : 'admin123',
                    'activo' => true,
                ]
            );
            $this->command->info("🔑 Contraseña maestra para usuario actual ({$currentUser->email}): " . ($tipo === 'super_admin' ? 'master123' : 'admin123'));
        }

        // ============================================================
        // 5. RESUMEN FINAL
        // ============================================================
        $this->command->info("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info('🎉 CONTRASEÑAS MAESTRAS CONFIGURADAS CORRECTAMENTE');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("📋 Super Admin: yeahme200120@gmail.com → contraseña maestra: master123");
        $this->command->info("📋 Administrador: {$admin->email} → contraseña maestra: admin123");
        
        $totalContrasenas = ContrasenaMaestra::count();
        $this->command->info("📋 Total de contraseñas maestras registradas: {$totalContrasenas}");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}