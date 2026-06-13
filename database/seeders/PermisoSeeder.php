<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================================
        // 1. PERMISOS ESTÁNDAR POR MÓDULO (ver, crear, editar, eliminar)
        // ============================================================
        $acciones = ['ver', 'crear', 'editar', 'eliminar'];
        $modulos = [
            'dashboard',
            'empresas',
            'licencias',
            'inventario',
            'compras',
            'proveedores',
            'ventas',
            'facturacion',
            'clientes',
            'cobranza',
            'formaspago',
            'notificaciones',
            'impresoras',
            'ticket',
            'usuarios',
            'roles',
            'reportes',
            'respaldos',
            'insumos',
            'unidades_medida',
            'caja',
            'productos',
            'categorias',
            'cotizaciones',
            'carrito',
            'sucursales',
            'contrasenas_maestras',
        ];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Permission::firstOrCreate([
                    'name' => "{$accion}_{$modulo}",
                    'guard_name' => 'web'
                ]);
            }
        }

        // ============================================================
        // 2. PERMISOS PARA MENÚS HIJOS Y VISTAS ESPECÍFICAS
        // ============================================================
        $permisosHijos = [
            // Empresas
            'ver_empresas',
            'ver_nueva_empresa',

            // Proveedores
            'ver_lista_proveedores',
            'ver_nuevo_proveedor',

            // Impresoras
            'ver_lista_impresoras',
            'ver_nueva_impresora',

            // Ticket
            'ver_general_ticket',
            'ver_diseno_ticket',

            // Inventario
            'ver_productos',
            'ver_categorias',
            'ver_movimientos',
            'ver_insumos',
            'ver_unidades_medida',

            // Compras
            'ver_ordenes',
            'ver_recepciones',

            // Cotizaciones
            'ver_cotizaciones',

            // Facturación
            'ver_facturas',
            'ver_timbrado',

            // Caja
            'ver_apertura_cierre',
            'ver_arqueos',
            'ver_cajas',
            'ver_lista_cajas',
            'ver_cierres_caja',

            // Cobranza
            'ver_creditos',
            'ver_historial',
            'ver_condonaciones',

            // Notificaciones
            'ver_correo',
            'ver_whatsapp',

            // Respaldos
            'ver_generar_respaldo',
            'ver_importar',

            // Clientes por ámbito
            'ver_mis_clientes',
            'ver_clientes_sucursal',
            'ver_todos_clientes',

            // Ventas por ámbito
            'ver_mis_ventas',
            'ver_ventas_sucursal',
            'ver_todas_ventas',

            // Sucursales
            'ver_sucursales',

            // Contraseñas Maestras
            'ver_contrasenas_maestras',
        ];

        foreach ($permisosHijos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);
        }

        // ============================================================
        // 3. PERMISOS ESPECIALES (ACCIONES ESPECÍFICAS)
        // ============================================================
        $permisosEspeciales = [
            // ===== ROLES Y PERMISOS (SOLO SUPER ADMIN) =====
            'modificar_permisos_usuarios',
            'modificar_permisos_roles',

            // ===== CONTRASEÑAS MAESTRAS =====
            'crear_contrasenas_maestras',
            'eliminar_contrasenas_maestras',

            // ===== CAJA - OPERACIONES =====
            'abrir_caja',
            'cerrar_caja',
            'cerrar_caja_ajena',
            'ver_cierres_caja',
            'realizar_arqueo',
            'finalizar_arqueo',
            'ver_arqueos_historial',
            'transferir_entre_cajas',
            'autorizar_transferencia',
            'autorizar_movimiento',
            'ver_configuracion_caja',
            'ver_lista_cajas',
            'ver_movimientos_caja',
            'registrar_movimiento_caja',
            'ver_autorizaciones_caja',
            'ver_transferencias_caja',
            'solicitar_transferencia_caja',
            'ver_reporte_caja_diario',
            'ver_dashboard_caja',
            'imprimir_arqueo_caja',
            'exportar_reportes_caja',
            'crear_caja',
            'editar_caja',
            'eliminar_caja',

            // ===== VENTAS =====
            'cancelar_ventas',
            'ver_historial_ventas',
            'imprimir_ticket_venta',
            'reimprimir_ticket',
            'convertir_cotizacion',
            'imprimir_cotizacion',
            'cancelar_creditos',
            'ver_pagares',
            'imprimir_pagare',

            // ===== COBRANZA =====
            'registrar_cobro',
            'condonar_adeudo',
            'ver_historial_cobros',
            'ver_historial_cobranza',
            'cancelar_cobro',
            'registrar_abono',
            'pagar_pagare',

            // ===== FACTURACIÓN =====
            'timbrar_factura',
            'cancelar_factura',

            // ===== CONFIGURACIÓN =====
            'configurar_correo',
            'configurar_whatsapp',
            'configurar_ticket',
            'configurar_impresora',

            // ===== RESPALDOS =====
            'generar_respaldo',
            'importar_datos',
            'restaurar_respaldo',

            // ===== CARRITO =====
            'ver_carrito',
            'agregar_carrito',
            'editar_carrito',
            'eliminar_carrito',
            'limpiar_carrito',

            // ===== DASHBOARD =====
            'exportar_dashboard',
            'ver_graficas_dashboard',

            // ===== PRODUCTOS ESPECÍFICOS =====
            'generar_codigo_barras',
            'ver_stock_productos',
            'actualizar_stock_productos',
            'importar_productos',
            'exportar_productos',

            // ===== CLIENTES ESPECÍFICOS =====
            'importar_clientes',
            'exportar_clientes',
            'ver_historial_compras_cliente',

            // ===== REPORTES =====
            'exportar_reportes',
            'generar_reportes_personalizados',

            // ===== SUCURSALES =====
            'crear_sucursales',
            'editar_sucursales',
            'eliminar_sucursales',
        ];

        foreach ($permisosEspeciales as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);
        }

        // ============================================================
        // 4. CREAR ROLES
        // ============================================================
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        $cajero = Role::firstOrCreate(['name' => 'Cajero', 'guard_name' => 'web']);
        $cobrador = Role::firstOrCreate(['name' => 'Cobrador', 'guard_name' => 'web']);

        // ============================================================
        // 5. ASIGNAR PERMISOS A ROLES
        // ============================================================

        // ===== SUPER ADMIN: TODOS LOS PERMISOS =====
        $superAdmin->syncPermissions(Permission::all());

        // ===== ADMINISTRADOR: TODOS EXCEPTO PERMISOS RESTRINGIDOS =====
        $permisosAdmin = Permission::all()->filter(function ($permiso) {
            $excluir = [
                'empresas',
                'licencias',
                'notificaciones',
                'impresoras',
                // 🔥 IMPORTANTE: Admin NO debe tener estos permisos
                'modificar_permisos_usuarios',
                'modificar_permisos_roles',
                'crear_contrasenas_maestras',
                'eliminar_contrasenas_maestras',
            ];
            foreach ($excluir as $modulo) {
                if (str_contains($permiso->name, $modulo)) {
                    return false;
                }
            }
            return true;
        })->pluck('name')->toArray();

        $admin->syncPermissions($permisosAdmin);

        // ===== VENDEDOR =====
        $vendedor->syncPermissions([
            'ver_dashboard',
            'ver_productos',
            'ver_categorias',
            'ver_unidades_medida',
            'ver_insumos',
            'ver_proveedores',
            'ver_ventas',
            'crear_ventas',
            'ver_historial_ventas',
            'imprimir_ticket_venta',
            'ver_mis_ventas',
            'ver_cotizaciones',
            'crear_cotizaciones',
            'convertir_cotizacion',
            'imprimir_cotizacion',
            'ver_clientes',
            'crear_clientes',
            'editar_clientes',
            'ver_mis_clientes',
            'ver_creditos',
            'ver_pagares',
            'ver_carrito',
            'agregar_carrito',
            'editar_carrito',
            'eliminar_carrito',
            'limpiar_carrito',
        ]);

        // ===== CAJERO =====
        $cajero->syncPermissions([
            'ver_dashboard',
            'ver_dashboard_caja',
            'ver_productos',
            'ver_unidades_medida',
            'ver_ventas',
            'crear_ventas',
            'ver_historial_ventas',
            'imprimir_ticket_venta',
            'ver_mis_ventas',
            'ver_clientes',
            'crear_clientes',
            'ver_carrito',
            'agregar_carrito',
            'editar_carrito',
            'eliminar_carrito',
            'limpiar_carrito',
            'ver_caja',
            'ver_cajas',
            'ver_lista_cajas',
            'abrir_caja',
            'cerrar_caja',
            'ver_cierres_caja',
            'realizar_arqueo',
            'ver_arqueos_historial',
            'ver_apertura_cierre',
            'ver_arqueos',
            'ver_movimientos_caja',
            'registrar_movimiento_caja',
            'ver_transferencias_caja',
            'solicitar_transferencia_caja',
            'ver_reporte_caja_diario',
            'imprimir_arqueo_caja',
            'ver_cobranza',
            'registrar_cobro',
            'ver_creditos',
            'ver_historial_cobranza',
        ]);

        // ===== COBRADOR =====
        $cobrador->syncPermissions([
            'ver_dashboard',
            'ver_ventas',
            'ver_historial_ventas',
            'ver_clientes',
            'ver_cobranza',
            'registrar_cobro',
            'ver_historial_cobros',
            'ver_creditos',
            'ver_historial',
            'ver_condonaciones',
            'ver_historial_cobranza',
            'cancelar_cobro',
            'registrar_abono',
            'pagar_pagare',
            'ver_pagares',
            'imprimir_pagare',
        ]);

        // ============================================================
        // 6. PERMISOS ADICIONALES PARA ADMIN (excluyendo los restringidos)
        // ============================================================
        $permisosAdicionalesAdmin = [
            'ver_contrasenas_maestras',  // Solo ver, no crear/eliminar
            'ver_cajas',
            'cerrar_caja_ajena',
            'ver_sucursales',
            'crear_sucursales',
            'editar_sucursales',
            'eliminar_sucursales',
            'crear_caja',
            'editar_caja',
            'eliminar_caja',
        ];

        foreach ($permisosAdicionalesAdmin as $permiso) {
            if (Permission::where('name', $permiso)->exists()) {
                $admin->givePermissionTo($permiso);
            }
        }

        $this->command->info('✅ Permisos creados exitosamente!');
        $this->command->info('📋 Total de permisos: ' . Permission::count());

        // Mostrar resumen de permisos por rol
        $this->command->info("\n📊 Resumen de permisos por rol:");
        $this->command->info("   Super Admin: " . $superAdmin->permissions->count() . " permisos");
        $this->command->info("   Administrador: " . $admin->permissions->count() . " permisos");
        $this->command->info("   Vendedor: " . $vendedor->permissions->count() . " permisos");
        $this->command->info("   Cajero: " . $cajero->permissions->count() . " permisos");
        $this->command->info("   Cobrador: " . $cobrador->permissions->count() . " permisos");

        // Verificar que Admin NO tenga permisos restringidos
        $permisosRestringidos = ['modificar_permisos_usuarios', 'modificar_permisos_roles'];
        $this->command->info("\n🔒 Verificando permisos restringidos para Administrador:");
        foreach ($permisosRestringidos as $permiso) {
            $tiene = $admin->hasPermissionTo($permiso);
            $this->command->info($tiene ? "   ❌ Admin TIENE {$permiso} (ERROR)" : "   ✅ Admin NO tiene {$permiso} (OK)");
        }
    }
}