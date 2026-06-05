<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->call([
            LicenciaSeeder::class,              // 1. Licencias primero
            EmpresaSeeder::class,               // 2. Empresas
            ModuloSeeder::class,                // 3. Módulos del sistema
            MenuSeeder::class,                  // 4. Menús
            PermisoSeeder::class,               // 5. Permisos y roles
            UsuarioSeeder::class,               // 6. Usuarios (depende de empresas y roles)
            ClienteSeeder::class,               // 7. Clientes (depende de empresas)
            ProveedorSeeder::class,             // 8. Proveedores (depende de empresas)
            CategoriaSeeder::class,             // 9. Categorías (depende de empresas)
            UnidadMedidaSeeder::class,
            InsumoSeeder::class,                // 10. Insumos (depende de empresas y proveedores)
            ProductoSeeder::class,              // 11. Productos (depende de empresas, categorías, proveedores, insumos)
            InventarioMovimientoSeeder::class,  // 12. Movimientos (depende de productos, insumos, usuarios)
            CajaMovimientoSeeder::class,        // 13. Movimientos de caja para datos en las graficas
        ]);
    }
}