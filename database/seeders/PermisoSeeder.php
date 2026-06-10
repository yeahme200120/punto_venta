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

        // ===== CREAR TODOS LOS PERMISOS PRIMERO =====

        // Permisos estándar por módulo
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
        ];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Permission::firstOrCreate(['name' => "{$accion}_{$modulo}", 'guard_name' => 'web']);
            }
        }

        // ===== PERMISOS PARA MENÚS HIJOS =====
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
        ];

        foreach ($permisosHijos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ===== PERMISOS ESPECIALES =====
        $permisosEspeciales = [
            // Caja - Operaciones
            'abrir_caja',
            'cerrar_caja',
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

            // Ventas
            'cancelar_ventas',
            'ver_historial_ventas',
            'imprimir_ticket_venta',
            'reimprimir_ticket',
            'convertir_cotizacion',
            'imprimir_cotizacion',
            'cancelar_creditos',
            'ver_pagares',
            'imprimir_pagare',

            // Cobranza
            'registrar_cobro',
            'condonar_adeudo',
            'ver_historial_cobros',
            'ver_historial_cobranza',
            'cancelar_cobro',
            'registrar_abono',
            'pagar_pagare',

            // Facturación
            'timbrar_factura',
            'cancelar_factura',

            // Configuración
            'configurar_correo',
            'configurar_whatsapp',
            'configurar_ticket',
            'configurar_impresora',

            // Respaldos
            'generar_respaldo',
            'importar_datos',
            'restaurar_respaldo',

            // Carrito
            'ver_carrito',
            'agregar_carrito',
            'editar_carrito',
            'eliminar_carrito',
            'limpiar_carrito',

            // Dashboard
            'exportar_dashboard',
            'ver_graficas_dashboard',

            // Productos específicos
            'generar_codigo_barras',
            'ver_stock_productos',
            'actualizar_stock_productos',
            'importar_productos',
            'exportar_productos',

            // Clientes específicos
            'importar_clientes',
            'exportar_clientes',
            'ver_historial_compras_cliente',

            // Reportes
            'exportar_reportes',
            'generar_reportes_personalizados',
        ];

        foreach ($permisosEspeciales as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ===== CREAR ROLES =====
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        $cajero = Role::firstOrCreate(['name' => 'Cajero', 'guard_name' => 'web']);
        $cobrador = Role::firstOrCreate(['name' => 'Cobrador', 'guard_name' => 'web']);

        // ===== SUPER ADMIN: TODOS (sin restricciones) =====
        $superAdmin->syncPermissions(Permission::all());

        // ===== ADMINISTRADOR (todos excepto módulos restringidos) =====
        $admin->syncPermissions(Permission::all()->filter(function($permiso) {
            $excluir = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
            foreach ($excluir as $modulo) {
                if (str_contains($permiso->name, $modulo)) {
                    return false;
                }
            }
            return true;
        })->pluck('name')->toArray());

        // ===== VENDEDOR =====
        $vendedor->syncPermissions([
            // Dashboard
            'ver_dashboard',
            
            // Productos
            'ver_productos',
            'ver_categorias',
            'ver_unidades_medida',
            'ver_insumos',
            
            // Proveedores
            'ver_proveedores',
            
            // Ventas
            'ver_ventas',
            'crear_ventas',
            'ver_historial_ventas',
            'imprimir_ticket_venta',
            'ver_mis_ventas',
            
            // Cotizaciones
            'ver_cotizaciones',
            'crear_cotizaciones',
            'convertir_cotizacion',
            'imprimir_cotizacion',
            
            // Clientes
            'ver_clientes',
            'crear_clientes',
            'editar_clientes',
            'ver_mis_clientes',
            
            // Créditos
            'ver_creditos',
            'ver_pagares',
            
            // Carrito
            'ver_carrito',
            'agregar_carrito',
            'editar_carrito',
            'eliminar_carrito',
            'limpiar_carrito',
        ]);

        // ===== CAJERO =====
        $cajero->syncPermissions([
            // Dashboard
            'ver_dashboard',
            'ver_dashboard_caja',
            
            // Productos
            'ver_productos',
            'ver_unidades_medida',
            
            // Ventas
            'ver_ventas',
            'crear_ventas',
            'ver_historial_ventas',
            'imprimir_ticket_venta',
            'ver_mis_ventas',
            
            // Clientes
            'ver_clientes',
            'crear_clientes',
            
            // Carrito
            'ver_carrito',
            'agregar_carrito',
            'editar_carrito',
            'eliminar_carrito',
            'limpiar_carrito',
            
            // Caja
            'ver_caja',
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
            
            // Cobranza básica
            'ver_cobranza',
            'registrar_cobro',
            'ver_creditos',
            'ver_historial_cobranza',
        ]);

        // ===== COBRADOR =====
        $cobrador->syncPermissions([
            // Dashboard
            'ver_dashboard',
            
            // Ventas
            'ver_ventas',
            'ver_historial_ventas',
            
            // Clientes
            'ver_clientes',
            
            // Cobranza
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

        $this->command->info('✅ Permisos creados exitosamente!');
        $this->command->info('📋 Total de permisos: ' . Permission::count());
    }
}