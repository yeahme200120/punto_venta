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
            'dashboard', 'empresas', 'licencias', 'inventario', 'compras',
            'proveedores', 'ventas', 'facturacion', 'clientes', 'caja',
            'cobranza', 'formaspago', 'notificaciones', 'impresoras',
            'ticket', 'usuarios', 'roles', 'reportes', 'respaldos', 
            'insumos', 'unidades_medida', 'caja_arqueos'
        ];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                Permission::firstOrCreate(['name' => "{$accion}_{$modulo}", 'guard_name' => 'web']);
            }
        }

        // Permisos específicos para gestión de cajas
        $permisosCajas = [
            'ver_cajas_config', 'crear_cajas_config', 'editar_cajas_config', 'eliminar_cajas_config',
            'ver_cajas', 'crear_cajas', 'editar_cajas', 'eliminar_cajas'
        ];

        foreach ($permisosCajas as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Permisos para menús hijos
        $permisosHijos = [
            'ver_ver_todas', 'ver_nueva_empresa', 'ver_lista_proveedores', 'ver_nuevo_proveedor',
            'ver_lista_impresoras', 'ver_nueva_impresora', 'ver_general_ticket', 'ver_diseno_ticket',
            'ver_productos', 'ver_categorias', 'ver_movimientos', 'ver_ordenes', 'ver_recepciones',
            'ver_cotizaciones', 'ver_facturas', 'ver_timbrado', 'ver_apertura_cierre',
            'ver_creditos', 'ver_historial', 'ver_condonaciones', 'ver_arqueos',
            'ver_correo', 'ver_whatsapp', 'ver_generar_respaldo', 'ver_importar',
            'ver_insumos', 'ver_unidades_medida'
        ];

        foreach ($permisosHijos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ===== PERMISOS ESPECIALES - CREAR PRIMERO =====
        $permisosEspeciales = [
            // Permisos de Caja
            'abrir_caja', 'cerrar_caja', 'ver_cierres_caja',
            'realizar_arqueo', 'finalizar_arqueo', 'ver_arqueos_historial',
            'transferir_entre_cajas', 'autorizar_transferencia', 'autorizar_movimiento',
            
            // Permisos de Cobranza
            'registrar_cobro', 'condonar_adeudo', 'ver_historial_cobros',
            
            // Permisos de Facturación
            'timbrar_factura', 'cancelar_factura',
            
            // Permisos de Configuración
            'configurar_correo', 'configurar_whatsapp', 'configurar_ticket', 'configurar_impresora',
            
            // Permisos de Respaldos
            'generar_respaldo', 'importar_datos',
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
            'ver_dashboard',
            'ver_inventario', 'crear_inventario', 'editar_inventario', 'eliminar_inventario',
            'ver_productos', 'ver_categorias', 'ver_movimientos',
            'ver_insumos', 'crear_insumos', 'editar_insumos', 'eliminar_insumos',
            'ver_unidades_medida', 'crear_unidades_medida', 'editar_unidades_medida', 'eliminar_unidades_medida',
            'ver_compras', 'crear_compras', 'editar_compras',
            'ver_ordenes', 'ver_recepciones',
            'ver_proveedores', 'crear_proveedores', 'editar_proveedores', 'eliminar_proveedores',
            'ver_lista_proveedores', 'ver_nuevo_proveedor',
            'ver_ventas', 'crear_ventas', 'editar_ventas', 'eliminar_ventas',
            'ver_cotizaciones',
            'ver_facturacion', 'crear_facturacion', 'editar_facturacion',
            'timbrar_factura', 'cancelar_factura',
            'ver_facturas', 'ver_timbrado',
            'ver_clientes', 'crear_clientes', 'editar_clientes',
            
            // Permisos de Caja - Configuración
            'ver_caja', 'crear_caja', 'editar_caja', 'eliminar_caja',
            'ver_cajas_config', 'crear_cajas_config', 'editar_cajas_config', 'eliminar_cajas_config',
            'ver_cajas', 'crear_cajas', 'editar_cajas', 'eliminar_cajas',
            
            // Permisos de Caja - Operaciones
            'abrir_caja', 'cerrar_caja', 'ver_cierres_caja',
            'ver_caja_arqueos', 'crear_caja_arqueos', 'editar_caja_arqueos', 'eliminar_caja_arqueos',
            'realizar_arqueo', 'finalizar_arqueo', 'ver_arqueos_historial',
            'ver_apertura_cierre', 'ver_arqueos',
            'transferir_entre_cajas', 'autorizar_transferencia', 'autorizar_movimiento',
            
            // Permisos de Cobranza
            'ver_cobranza', 'registrar_cobro', 'condonar_adeudo', 'ver_historial_cobros',
            'ver_creditos', 'ver_historial', 'ver_condonaciones',
            
            // Otros permisos
            'ver_formaspago', 'editar_formaspago',
            'ver_ver_todas',
            'ver_notificaciones', 'editar_notificaciones',
            'configurar_correo', 'configurar_whatsapp',
            'ver_correo', 'ver_whatsapp',
            'ver_impresoras', 'crear_impresoras', 'editar_impresoras', 'eliminar_impresoras',
            'configurar_impresora',
            'ver_lista_impresoras', 'ver_nueva_impresora',
            'ver_ticket', 'editar_ticket', 'configurar_ticket',
            'ver_general_ticket', 'ver_diseno_ticket',
            'ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios',
            'ver_roles', 'crear_roles', 'editar_roles', 'eliminar_roles',
            'ver_reportes',
            'generar_respaldo', 'importar_datos',
        ]);

        // ===== VENDEDOR =====
        $vendedor->syncPermissions([
            'ver_dashboard',
            'ver_inventario',
            'ver_productos',
            'ver_insumos',
            'ver_unidades_medida',
            'ver_proveedores',
            'ver_ventas', 'crear_ventas',
            'ver_cotizaciones',
            'ver_clientes', 'crear_clientes',
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
            'ver_ventas', 'crear_ventas',
            'ver_cotizaciones',
            'ver_proveedores',
            'ver_facturacion', 'crear_facturacion',
            'ver_facturas',
            'ver_clientes', 'crear_clientes',
            
            // Permisos de Caja para Cajero
            'ver_caja',
            'abrir_caja', 'cerrar_caja',
            'realizar_arqueo', 'ver_arqueos_historial',
            'ver_apertura_cierre', 'ver_arqueos',
            
            'ver_cobranza', 'registrar_cobro',
            'ver_creditos',
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
            'ver_cobranza', 'registrar_cobro', 'ver_historial_cobros',
            'ver_creditos', 'ver_historial', 'ver_condonaciones',
        ]);
    }
}