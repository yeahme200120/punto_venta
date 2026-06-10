<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Modulo;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ===== DASHBOARD =====
        $dashboard = Modulo::where('nombre', 'Dashboard')->first();
        if ($dashboard) {
            Menu::updateOrCreate(
                ['modulo_id' => $dashboard->id, 'nombre' => 'Inicio'],
                ['ruta' => '/dashboard', 'icono' => '🏠', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_dashboard']
            );
        }

        // ===== EMPRESAS (SOLO SUPER ADMIN) =====
        $empresas = Modulo::where('nombre', 'Empresas')->first();
        if ($empresas) {
            $menuEmpresas = Menu::updateOrCreate(
                ['modulo_id' => $empresas->id, 'nombre' => 'Gestión'],
                ['icono' => '🏢', 'orden' => 1, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $empresas->id, 'menu_padre_id' => $menuEmpresas->id, 'nombre' => 'Ver todas'],
                ['ruta' => '/empresas', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_empresas']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $empresas->id, 'menu_padre_id' => $menuEmpresas->id, 'nombre' => 'Nueva empresa'],
                ['ruta' => '/empresas/create', 'orden' => 2, 'activo' => true, 'permiso' => 'crear_empresas']
            );
        }

        // ===== LICENCIAS (SOLO SUPER ADMIN) =====
        $licencias = Modulo::where('nombre', 'Licencias')->first();
        if ($licencias) {
            Menu::updateOrCreate(
                ['modulo_id' => $licencias->id, 'nombre' => 'Licencias'],
                ['ruta' => '/licencias', 'icono' => '📜', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_licencias']
            );
        }

        // ===== INVENTARIO =====
        $inventario = Modulo::where('nombre', 'Inventario')->first();
        if ($inventario) {
            $catInventario = Menu::updateOrCreate(
                ['modulo_id' => $inventario->id, 'nombre' => 'Catálogo'],
                ['icono' => '📋', 'orden' => 1, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $inventario->id, 'menu_padre_id' => $catInventario->id, 'nombre' => 'Productos'],
                ['ruta' => '/productos', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_productos']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $inventario->id, 'menu_padre_id' => $catInventario->id, 'nombre' => 'Insumos'],
                ['ruta' => '/insumos', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_insumos']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $inventario->id, 'menu_padre_id' => $catInventario->id, 'nombre' => 'Categorías'],
                ['ruta' => '/categorias', 'orden' => 3, 'activo' => true, 'permiso' => 'ver_categorias']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $inventario->id, 'menu_padre_id' => $catInventario->id, 'nombre' => 'Unidades de medida'],
                ['ruta' => '/unidades-medida', 'orden' => 4, 'activo' => true, 'permiso' => 'ver_unidades_medida']
            );
            // Movimientos de inventario
            Menu::updateOrCreate(
                ['modulo_id' => $inventario->id, 'nombre' => 'Movimientos'],
                ['ruta' => '/inventario/movimientos', 'icono' => '🔄', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_inventario']
            );
        }

        // ===== COMPRAS (MÓDULO INACTIVO) =====
        $compras = Modulo::where('nombre', 'Compras')->first();
        if ($compras && $compras->activo) {
            Menu::updateOrCreate(
                ['modulo_id' => $compras->id, 'nombre' => 'Órdenes'],
                ['ruta' => '/compras', 'icono' => '📝', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_compras']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $compras->id, 'nombre' => 'Recepciones'],
                ['ruta' => '/compras/recepciones', 'icono' => '📥', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_compras']
            );
        }

        // ===== PROVEEDORES =====
        $proveedores = Modulo::where('nombre', 'Proveedores')->first();
        if ($proveedores) {
            $menuProv = Menu::updateOrCreate(
                ['modulo_id' => $proveedores->id, 'nombre' => 'Gestión'],
                ['icono' => '🚚', 'orden' => 1, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $proveedores->id, 'menu_padre_id' => $menuProv->id, 'nombre' => 'Lista'],
                ['ruta' => '/proveedores', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_proveedores']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $proveedores->id, 'menu_padre_id' => $menuProv->id, 'nombre' => 'Nuevo'],
                ['ruta' => '/proveedores/create', 'orden' => 2, 'activo' => true, 'permiso' => 'crear_proveedores']
            );
        }

        // ===== VENTAS =====
        $ventas = Modulo::where('nombre', 'Ventas')->first();
        if ($ventas) {
            Menu::updateOrCreate(
                ['modulo_id' => $ventas->id, 'nombre' => 'Punto de Venta'],
                ['ruta' => '/ventas', 'icono' => '🛒', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_ventas']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $ventas->id, 'nombre' => 'Cotizaciones'],
                ['ruta' => '/cotizaciones', 'icono' => '📝', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_cotizaciones']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $ventas->id, 'nombre' => 'Historial de Ventas'],
                ['ruta' => '/ventas/historial', 'icono' => '📋', 'orden' => 3, 'activo' => true, 'permiso' => 'ver_historial_ventas']
            );
        }

        // ===== FACTURACION (MÓDULO INACTIVO) =====
        $facturacion = Modulo::where('nombre', 'Facturacion')->first();
        if ($facturacion && $facturacion->activo) {
            Menu::updateOrCreate(
                ['modulo_id' => $facturacion->id, 'nombre' => 'Facturas'],
                ['ruta' => '/facturacion', 'icono' => '🧾', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_facturacion']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $facturacion->id, 'nombre' => 'Timbrado'],
                ['ruta' => '/facturacion/timbrado', 'icono' => '✅', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_facturacion']
            );
        }

        // ===== CLIENTES =====
        $clientes = Modulo::where('nombre', 'Clientes')->first();
        if ($clientes) {
            Menu::updateOrCreate(
                ['modulo_id' => $clientes->id, 'nombre' => 'Clientes'],
                ['ruta' => '/clientes', 'icono' => '👥', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_clientes']
            );
        }

        // ===== CAJA =====
        $caja = Modulo::where('nombre', 'Caja')->first();
        if ($caja) {
            // Configuración
            $configCaja = Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'nombre' => 'Configuración'],
                ['icono' => '⚙️', 'orden' => 0, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $configCaja->id, 'nombre' => 'Listado de Cajas'],
                ['ruta' => '/caja/cajas', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_cajas']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $configCaja->id, 'nombre' => 'Nueva Caja'],
                ['ruta' => '/caja/cajas/create', 'orden' => 2, 'activo' => true, 'permiso' => 'crear_caja']
            );

            // Operaciones
            $menuCaja = Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'nombre' => 'Operaciones'],
                ['icono' => '💵', 'orden' => 1, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $menuCaja->id, 'nombre' => 'Apertura/Cierre'],
                ['ruta' => '/caja/apertura', 'orden' => 1, 'activo' => true, 'permiso' => 'abrir_caja']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $menuCaja->id, 'nombre' => 'Movimientos'],
                ['ruta' => '/caja/operaciones', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_movimientos_caja']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $menuCaja->id, 'nombre' => 'Arqueos'],
                ['ruta' => '/caja/arqueos', 'orden' => 3, 'activo' => true, 'permiso' => 'realizar_arqueo']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $menuCaja->id, 'nombre' => 'Transferencias'],
                ['ruta' => '/caja/transferencias', 'orden' => 4, 'activo' => true, 'permiso' => 'ver_transferencias_caja']
            );

            // Autorizaciones
            $authCaja = Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'nombre' => 'Autorizaciones'],
                ['icono' => '🔐', 'orden' => 2, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $authCaja->id, 'nombre' => 'Pendientes'],
                ['ruta' => '/caja/autorizaciones', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_autorizaciones_caja']
            );

            // Reportes
            $reportesCaja = Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'nombre' => 'Reportes'],
                ['icono' => '📊', 'orden' => 3, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $reportesCaja->id, 'nombre' => 'Dashboard'],
                ['ruta' => '/dashboard-caja', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_dashboard_caja']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $caja->id, 'menu_padre_id' => $reportesCaja->id, 'nombre' => 'Reporte Diario'],
                ['ruta' => '/reportes/caja-dashboard', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_reporte_caja_diario']
            );
        }

        // ===== COBRANZA =====
        $cobranza = Modulo::where('nombre', 'Cobranza')->first();
        if ($cobranza) {
            $menuCobranza = Menu::updateOrCreate(
                ['modulo_id' => $cobranza->id, 'nombre' => 'Gestión'],
                ['icono' => '💰', 'orden' => 1, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $cobranza->id, 'menu_padre_id' => $menuCobranza->id, 'nombre' => 'Créditos'],
                ['ruta' => '/cobranza/creditos', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_cobranza']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $cobranza->id, 'menu_padre_id' => $menuCobranza->id, 'nombre' => 'Historial'],
                ['ruta' => '/cobranza/historial', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_historial_cobranza']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $cobranza->id, 'menu_padre_id' => $menuCobranza->id, 'nombre' => 'Condonaciones'],
                ['ruta' => '/cobranza/condonaciones', 'orden' => 3, 'activo' => true, 'permiso' => 'ver_condonaciones']
            );
        }

        // ===== FORMAS DE PAGO =====
        $formasPago = Modulo::where('nombre', 'FormasPago')->first();
        if ($formasPago) {
            Menu::updateOrCreate(
                ['modulo_id' => $formasPago->id, 'nombre' => 'Catálogo'],
                ['ruta' => '/formas-pago', 'icono' => '💳', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_formaspago']
            );
        }

        // ===== NOTIFICACIONES (MÓDULO INACTIVO) =====
        $notificaciones = Modulo::where('nombre', 'Notificaciones')->first();
        if ($notificaciones && $notificaciones->activo) {
            Menu::updateOrCreate(
                ['modulo_id' => $notificaciones->id, 'nombre' => 'Correo'],
                ['ruta' => '/notificaciones/correo', 'icono' => '📧', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_notificaciones']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $notificaciones->id, 'nombre' => 'WhatsApp'],
                ['ruta' => '/notificaciones/whatsapp', 'icono' => '💬', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_notificaciones']
            );
        }

        // ===== IMPRESORAS (MÓDULO INACTIVO) =====
        $impresoras = Modulo::where('nombre', 'Impresoras')->first();
        if ($impresoras && $impresoras->activo) {
            Menu::updateOrCreate(
                ['modulo_id' => $impresoras->id, 'nombre' => 'Gestión'],
                ['ruta' => '/impresoras', 'icono' => '🖨️', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_impresoras']
            );
        }

        // ===== TICKET =====
        $ticket = Modulo::where('nombre', 'Ticket')->first();
        if ($ticket) {
            $menuTicket = Menu::updateOrCreate(
                ['modulo_id' => $ticket->id, 'nombre' => 'Configuración'],
                ['icono' => '🎫', 'orden' => 1, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $ticket->id, 'menu_padre_id' => $menuTicket->id, 'nombre' => 'General'],
                ['ruta' => '/ticket', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_ticket']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $ticket->id, 'menu_padre_id' => $menuTicket->id, 'nombre' => 'Diseño'],
                ['ruta' => '/ticket/diseno', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_ticket']
            );
        }

        // ===== USUARIOS =====
        $usuarios = Modulo::where('nombre', 'Usuarios')->first();
        if ($usuarios) {
            $adminUsuarios = Menu::updateOrCreate(
                ['modulo_id' => $usuarios->id, 'nombre' => 'Administración'],
                ['icono' => '⚙️', 'orden' => 1, 'activo' => true, 'permiso' => null]
            );
            Menu::updateOrCreate(
                ['modulo_id' => $usuarios->id, 'menu_padre_id' => $adminUsuarios->id, 'nombre' => 'Usuarios'],
                ['ruta' => '/usuarios', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_usuarios']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $usuarios->id, 'menu_padre_id' => $adminUsuarios->id, 'nombre' => 'Roles'],
                ['ruta' => '/roles', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_roles']
            );
        }

        // ===== REPORTES =====
        $reportes = Modulo::where('nombre', 'Reportes')->first();
        if ($reportes) {
            Menu::updateOrCreate(
                ['modulo_id' => $reportes->id, 'nombre' => 'Ventas'],
                ['ruta' => '/reportes/ventas', 'icono' => '📊', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_reportes']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $reportes->id, 'nombre' => 'Inventario'],
                ['ruta' => '/reportes/inventario', 'icono' => '📋', 'orden' => 2, 'activo' => true, 'permiso' => 'ver_reportes']
            );
            Menu::updateOrCreate(
                ['modulo_id' => $reportes->id, 'nombre' => 'Cobranza'],
                ['ruta' => '/reportes/cobranza', 'icono' => '💰', 'orden' => 3, 'activo' => true, 'permiso' => 'ver_reportes']
            );
        }

        // ===== RESPALDOS =====
        $respaldos = Modulo::where('nombre', 'Respaldos')->first();
        if ($respaldos) {
            Menu::updateOrCreate(
                ['modulo_id' => $respaldos->id, 'nombre' => 'Generar respaldo'],
                ['ruta' => '/respaldos', 'icono' => '💾', 'orden' => 1, 'activo' => true, 'permiso' => 'ver_respaldos']
            );
        }
    }
}