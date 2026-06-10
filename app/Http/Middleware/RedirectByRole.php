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
        // Si no está autenticado, continuar
        if (!Auth::check()) {
            return $next($request);
        }

        // ❗ IGNORAR RUTAS DE AUTENTICACIÓN
        $authRoutes = ['login', 'logout', 'password/*', 'verify-email', 'register'];
        foreach ($authRoutes as $route) {
            if ($request->routeIs($route) || $request->is($route)) {
                return $next($request);
            }
        }

        // ✅ PERMITIR RUTAS DEL CARRITO PARA TODOS LOS USUARIOS AUTENTICADOS
        if ($request->is('carrito') || $request->is('carrito/*')) {
            Log::info('Ruta de carrito permitida para: ' . $request->path());
            return $next($request);
        }

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 🔹 SUPER ADMIN: Acceso total
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // 🔹 OBTENER RUTAS PERMITIDAS DINÁMICAMENTE SEGÚN PERMISOS DEL USUARIO
        $rutasPermitidas = $this->obtenerRutasPermitidas($user);

        // Verificar si la ruta actual está permitida
        $rutaActual = $request->path();
        $accesoPermitido = false;

        foreach ($rutasPermitidas as $patron) {
            if ($request->is($patron)) {
                $accesoPermitido = true;
                break;
            }
        }

        if ($accesoPermitido) {
            return $next($request);
        }

        // Si no tiene acceso, redirigir al dashboard o a su vista principal
        $mensaje = '🔒 Acceso denegado. No tienes permiso para acceder a: ' . $rutaActual;
        Log::warning('Acceso denegado (RedirectByRole): ' . $rutaActual . ' - Usuario: ' . $user->email);

        $rutaRedireccion = $this->getHomeRoute($user);

        return redirect()->route($rutaRedireccion)
            ->with('swal_error', $mensaje);
    }

    /**
     * Obtener rutas permitidas dinámicamente según los permisos del usuario
     */
    private function obtenerRutasPermitidas($user)
    {
        $rutasPermitidas = [];

        // Obtener todos los menús a los que el usuario tiene acceso
        $menus = Menu::where('activo', true)
            ->whereNotNull('ruta')
            ->get();

        foreach ($menus as $menu) {
            // Si el menú tiene un permiso específico, verificar que el usuario lo tenga
            if ($menu->permiso) {
                if ($user->can($menu->permiso)) {
                    $rutasPermitidas[] = $this->convertirRutaAPatron($menu->ruta);
                }
            } else {
                // Si no tiene permiso específico, permitir acceso (fallback)
                $rutasPermitidas[] = $this->convertirRutaAPatron($menu->ruta);
            }
        }

        // Agregar rutas base siempre permitidas
        $rutasBase = [
            'dashboard',
            'perfil',
            'perfil/*',
            'logout',
            'carrito',             // Carrito principal
            'carrito/*',
        ];

        $rutasPermitidas = array_merge($rutasPermitidas, $rutasBase);

        // Eliminar duplicados y vacíos
        $rutasPermitidas = array_unique(array_filter($rutasPermitidas));

        Log::info('Rutas permitidas para usuario ' . $user->email . ': ' . json_encode($rutasPermitidas));

        return $rutasPermitidas;
    }

    /**
     * Convertir una ruta a patrón para matching
     * Ej: /clientes -> clientes*
     *     /clientes/create -> clientes/create
     *     /categorias -> categorias*
     */
    private function convertirRutaAPatron($ruta)
    {
        // Eliminar slash inicial
        $ruta = ltrim($ruta, '/');

        // Si la ruta tiene subrutas (ej: clientes/create), mantenerla exacta
        // y también permitir subrutas hijas
        if (strpos($ruta, '/') !== false) {
            // Para rutas con múltiples segmentos, permitir exactamente esa ruta
            // y sus subrutas
            return $ruta . '*';
        }

        // Para rutas simples, permitir toda la rama
        return $ruta . '*';
    }

    /**
     * Obtener la ruta de inicio según el rol del usuario
     */
    private function getHomeRoute($user)
    {
        $role = $user->getRoleNames()->first();

        // Priorizar la primera ruta a la que tenga acceso
        $rutasPrioritarias = [
            'ventas.index',
            'cajas.operaciones',
            'cobranza.index',
            'dashboard',
        ];

        foreach ($rutasPrioritarias as $ruta) {
            try {
                $path = route($ruta, [], false);
                $path = ltrim($path, '/');

                // Verificar si el usuario tiene acceso a esta ruta
                $menus = \App\Models\Menu::where('ruta', 'LIKE', '%' . $path . '%')->first();
                if ($menus) {
                    if (!$menus->permiso || $user->can($menus->permiso)) {
                        return $ruta;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback por rol
        $roleRoutes = [
            'Super Admin' => 'dashboard',
            'Administrador' => 'dashboard',
            'Vendedor' => 'ventas.index',
            'Cajero' => 'cajas.operaciones',
            'Cobrador' => 'cobranza.index',
        ];

        return $roleRoutes[$role] ?? 'productos.index';
    }
}