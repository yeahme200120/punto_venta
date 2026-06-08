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
            // Módulos base del sistema
            LicenciaSeeder::class,              // 1. Licencias primero
            EmpresaSeeder::class,               // 2. Empresas
            ModuloSeeder::class,                // 3. Módulos del sistema
            MenuSeeder::class,                  // 4. Menús
            PermisoSeeder::class,               // 5. Permisos y roles
            UsuarioSeeder::class,               // 6. Usuarios (depende de empresas y roles)
            
            // Datos maestros
            ClienteSeeder::class,               // 7. Clientes (depende de empresas)
            ProveedorSeeder::class,             // 8. Proveedores (depende de empresas)
            CategoriaSeeder::class,             // 9. Categorías (depende de empresas)
            UnidadMedidaSeeder::class,          // 10. Unidades de medida
            
            // Inventario
            InsumoSeeder::class,                // 11. Insumos (depende de empresas y proveedores)
            ProductoSeeder::class,              // 12. Productos (depende de empresas, categorías, proveedores, insumos)
            InventarioMovimientoSeeder::class,  // 13. Movimientos de inventario (depende de productos, insumos, usuarios)
            
            // ==================== CAJA (SISTEMA POS) ====================
            ContrasenaMaestraSeeder::class,     // 14. Contraseñas maestras para autorizaciones
            CajaSeeder::class,                  // 15. Cajas (depende de empresas y sucursales)
            CajaAperturaSeeder::class,          // 16. Aperturas de caja (depende de cajas y usuarios)
            CajaMovimientoSeeder::class,        // 17. Movimientos de caja (depende de aperturas)
            CajaArqueoSeeder::class,            // 18. Arqueos de caja (depende de aperturas)
            CajaTransferenciaSeeder::class,     // 19. Transferencias entre cajas (depende de aperturas)

            TicketConfiguracionSeeder::class,    // 20. Configuracion de los tickets
            FormaPagoSeeder::class,              // 21. Catalogos de tipos de pago
        ]);
    }
}