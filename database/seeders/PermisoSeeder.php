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
            'caja'
        ];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Permission::firstOrCreate(['name' => "{$accion}_{$modulo}", 'guard_name' => 'web']);
            }
        }

        // ===== PERMISOS PARA MENÚS HIJOS =====
        $permisosHijos = [
            'ver_empresas',
            'ver_nueva_empresa',
            'ver_lista_proveedores',
            'ver_nuevo_proveedor',
            'ver_lista_impresoras',
            'ver_nueva_impresora',
            'ver_general_ticket',
            'ver_diseno_ticket',
            'ver_productos',
            'ver_categorias',
            'ver_movimientos',
            'ver_ordenes',
            'ver_recepciones',
            'ver_cotizaciones',
            'ver_facturas',
            'ver_timbrado',
            'ver_apertura_cierre',
            'ver_creditos',
            'ver_historial',
            'ver_condonaciones',
            'ver_arqueos',
            'ver_correo',
            'ver_whatsapp',
            'ver_generar_respaldo',
            'ver_importar',
            'ver_insumos',
            'ver_unidades_medida'
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

        // ===== SUPER ADMIN: TODOS =====
        $superAdmin->syncPermissions(Permission::all());

        // ===== ADMINISTRADOR =====
        $admin->syncPermissions([
            // Dashboard
            'ver_dashboard',

            // ===== INVENTARIO =====
            'ver_inventario',
            'ver_productos',
            'ver_categorias',
            'ver_movimientos',
            'ver_insumos',
            'crear_insumos',
            'editar_insumos',
            'eliminar_insumos',  // ← AGREGADO
            'ver_unidades_medida',
            'crear_unidades_medida',
            'editar_unidades_medida',
            'eliminar_unidades_medida',  // ← AGREGADO

            // ===== PROVEEDORES =====
            'ver_proveedores',
            'crear_proveedores',
            'editar_proveedores',
            'ver_lista_proveedores',
            'ver_nuevo_proveedor',

            // ===== VENTAS =====
            'ver_ventas',
            'crear_ventas',
            'editar_ventas',
            'cancelar_ventas',
            'ver_historial_ventas',
            'imprimir_ticket_venta',
            'ver_cotizaciones',
            'convertir_cotizacion',
            'imprimir_cotizacion',

            // ===== CLIENTES =====
            'ver_clientes',
            'crear_clientes',
            'editar_clientes',
            'eliminar_clientes',  // ← AGREGADO

            // ===== CRÉDITOS =====
            'ver_creditos',
            'cancelar_creditos',
            'ver_pagares',
            'imprimir_pagare',

            // ===== CAJA =====
            'ver_caja',
            'crear_caja',
            'editar_caja',
            'eliminar_caja',
            'abrir_caja',
            'cerrar_caja',
            'ver_cierres_caja',
            'realizar_arqueo',
            'finalizar_arqueo',
            'ver_arqueos_historial',
            'ver_apertura_cierre',
            'ver_arqueos',
            'ver_movimientos_caja',
            'registrar_movimiento_caja',
            'transferir_entre_cajas',
            'autorizar_transferencia',
            'autorizar_movimiento',
            'ver_autorizaciones_caja',
            'ver_transferencias_caja',
            'solicitar_transferencia_caja',
            'ver_reporte_caja_diario',
            'ver_dashboard_caja',
            'imprimir_arqueo_caja',
            'exportar_reportes_caja',
            'ver_configuracion_caja',
            'ver_lista_cajas',

            // ===== COBRANZA =====
            'ver_cobranza',
            'registrar_cobro',
            'condonar_adeudo',
            'ver_historial_cobros',
            'ver_historial',
            'ver_condonaciones',
            'ver_historial_cobranza',
            'cancelar_cobro',
            'registrar_abono',
            'pagar_pagare',

            // ===== FORMAS DE PAGO =====
            'ver_formaspago',
            'crear_formaspago',   // ← AGREGADO
            'editar_formaspago',
            'eliminar_formaspago', // ← AGREGADO

            // ===== USUARIOS =====
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',

            // ===== ROLES =====
            'ver_roles',
            'crear_roles',
            'editar_roles',
            'eliminar_roles',

            // ===== REPORTES =====
            'ver_reportes',

            // ===== ADICIONALES =====
            'ver_cotizaciones',
        ]);

        // ===== VENDEDOR =====
        $vendedor->syncPermissions([
            'ver_dashboard',
            'ver_inventario',
            'ver_productos',
            'ver_insumos',
            'ver_unidades_medida',
            'ver_proveedores',
            'ver_ventas',
            'crear_ventas',
            'imprimir_ticket_venta',
            'ver_historial_ventas',
            'ver_cotizaciones',
            'convertir_cotizacion',
            'imprimir_cotizacion',
            'ver_clientes',
            'crear_clientes',
            'ver_cobranza',
            'ver_creditos',
        ]);

        // ===== CAJERO =====
        $cajero->syncPermissions([
            'ver_dashboard',
            'ver_inventario',
            'ver_productos',
            'ver_insumos',
            'ver_unidades_medida',
            'ver_ventas',
            'crear_ventas',
            'imprimir_ticket_venta',
            'ver_historial_ventas',
            'ver_cotizaciones',
            'ver_proveedores',
            'ver_facturacion',
            'crear_facturacion',
            'ver_facturas',
            'ver_clientes',
            'crear_clientes',

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
            'ver_dashboard_caja',
            'imprimir_arqueo_caja',

            // Cobranza básica
            'ver_cobranza',
            'registrar_cobro',
            'ver_creditos',
            'ver_historial_cobranza',
        ]);

        // ===== COBRADOR =====
        $cobrador->syncPermissions([
            'ver_dashboard',
            'ver_ventas',
            'ver_facturacion',
            'ver_facturas',
            'ver_clientes',
            'ver_insumos',
            'ver_proveedores',
            'ver_unidades_medida',
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
    }
}