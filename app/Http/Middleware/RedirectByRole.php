<?php
// app/Http/Middleware/RedirectByRole.php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectByRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Ignorar rutas de autenticación
        $authRoutes = ['login', 'logout', 'password/*', 'verify-email', 'register'];
        foreach ($authRoutes as $route) {
            if ($request->routeIs($route) || $request->is($route)) {
                return $next($request);
            }
        }

        // Permitir carrito para todos
        if ($request->is('carrito') || $request->is('carrito/*')) {
            return $next($request);
        }

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Super Admin: acceso total
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        $rutasPermitidas = $this->obtenerRutasPermitidas($user);
        $rutaActual = $request->path();

        foreach ($rutasPermitidas as $patron) {
            if ($request->is($patron)) {
                return $next($request);
            }
        }

        Log::warning('Acceso denegado (RedirectByRole): ' . $rutaActual . ' - Usuario: ' . $user->email);
        return redirect()->route($this->getHomeRoute($user))
            ->with('swal_error', '🔒 Acceso denegado: ' . $rutaActual);
    }

    private function obtenerRutasPermitidas($user)
    {
        $rutas = [];

        // ✅ Rutas de la tabla menus
        $menus = Menu::where('activo', true)->whereNotNull('ruta')->get();
        foreach ($menus as $menu) {
            if (!$menu->permiso || $user->can($menu->permiso)) {
                $rutas[] = $this->convertirRutaAPatron($menu->ruta);
            }
        }

        // ✅ Rutas por permisos
        $rutas = array_merge($rutas, $this->obtenerRutasPorPermisos($user));

        // ✅ Rutas base
        $rutas = array_merge($rutas, ['dashboard', 'perfil', 'perfil/*', 'logout', 'carrito', 'carrito/*']);

        $rutas = array_unique(array_filter($rutas));
        
        Log::info('Rutas permitidas para ' . $user->email . ': ' . json_encode(array_values($rutas)));

        return $rutas;
    }

    private function obtenerRutasPorPermisos($user)
    {
        $rutas = [];

        // ✅ MAPEO COMPLETO DE PERMISOS A RUTAS
        $mapa = [
            // Caja
            'ver_caja'              => ['caja*', 'caja/*', 'cajas*', 'cajas/*'],
            'abrir_caja'            => ['caja/abrir', 'caja/abrir/*'],
            'cerrar_caja'           => ['caja/cerrar', 'caja/cerrar/*'],
            'ver_apertura_cierre'   => ['caja/apertura*', 'caja/apertura/*'],
            'ver_movimientos_caja'  => ['caja/operaciones*', 'caja/operaciones/*'],
            'registrar_movimiento_caja' => ['caja/movimiento*', 'caja/movimiento/*'],
            'realizar_arqueo'       => ['caja/arqueo*', 'caja/arqueo/*', 'caja/arqueos*', 'caja/arqueos/*'],
            'ver_transferencias_caja' => ['caja/transferencia*', 'caja/transferencias*'],
            'ver_autorizaciones_caja' => ['caja/autorizaciones*', 'caja/autorizacion*'],
            'ver_reporte_caja_diario' => ['caja/reporte*', 'caja/reporte/*'],
            'ver_dashboard_caja'    => ['dashboard-caja*', 'dashboard-caja/*'],

            // Clientes
            'ver_clientes'          => ['clientes*', 'clientes/*'],

            // Productos
            'ver_productos'         => ['productos*', 'productos/*'],

            // Proveedores
            'ver_proveedores'       => ['proveedores*', 'proveedores/*'],

            // Categorías
            'ver_categorias'        => ['categorias*', 'categorias/*'],

            // Insumos
            'ver_insumos'           => ['insumos*', 'insumos/*'],

            // Unidades de medida
            'ver_unidades_medida'   => ['unidades-medida*', 'unidades-medida/*'],

            // Ventas
            'ver_ventas'            => ['ventas*', 'ventas/*'],

            // Cotizaciones
            'ver_cotizaciones'      => ['cotizaciones*', 'cotizaciones/*'],

            // Cobranza
            'ver_cobranza'          => ['cobranza*', 'cobranza/*'],

            // Reportes
            'ver_reportes'          => ['reportes*', 'reportes/*'],

            // Usuarios
            'ver_usuarios'          => ['usuarios*', 'usuarios/*'],

            // Roles
            'ver_roles'             => ['roles*', 'roles/*'],

            // Respaldos
            'ver_respaldos'         => ['respaldos*', 'respaldos/*'],

            // Empresas
            'ver_empresas'          => ['empresas*', 'empresas/*', 'empresa/*'],

            // Licencias
            'ver_licencias'         => ['licencias*', 'licencias/*'],

            // Sucursales
            'ver_sucursales'        => ['sucursales*', 'sucursales/*', 'sucursal/*'],

            // Impresoras
            'ver_impresoras'        => ['impresoras*', 'impresoras/*'],

            // Formas de pago
            'ver_formaspago'        => ['formas-pago*', 'formas-pago/*'],

            // Ticket
            'ver_ticket'            => ['ticket*', 'ticket/*'],

            // Inventario
            'ver_inventario'        => ['inventario*', 'inventario/*'],
        ];

        foreach ($mapa as $permiso => $rutasPermitidas) {
            if ($user->can($permiso)) {
                $rutas = array_merge($rutas, $rutasPermitidas);
            }
        }

        return $rutas;
    }

    private function convertirRutaAPatron($ruta)
    {
        $ruta = ltrim($ruta, '/');
        return (strpos($ruta, '/') !== false) ? $ruta . '*' : $ruta . '*';
    }

    private function getHomeRoute($user)
    {
        $role = $user->getRoleNames()->first() ?? '';
        
        $roleRoutes = [
            'Super Admin'    => 'dashboard',
            'Administrador'  => 'dashboard',
            'Vendedor'       => 'ventas.index',
            'Cajero'         => 'cajas.apertura',
            'Cobrador'       => 'cobranza.index',
        ];

        return $roleRoutes[$role] ?? 'dashboard';
    }
}