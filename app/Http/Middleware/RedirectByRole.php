<?php
// app/Http/Middleware/RedirectByRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 🔹 SUPER ADMIN: Acceso total
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // 🔹 ADMINISTRADOR: Acceso a casi todo
        if ($user->hasRole('Administrador')) {
            // Rutas públicas que siempre puede acceder
            $publicRoutes = ['dashboard', 'perfil', 'logout'];
            if (in_array($request->path(), $publicRoutes)) {
                return $next($request);
            }
            
            // Verificar si la ruta actual está permitida
            $allowedPrefixes = [
                'dashboard', 'caja', 'ventas', 'clientes', 'productos',
                'insumos', 'categorias', 'unidades-medida', 'proveedores',
                'cobranza', 'formas-pago', 'ticket', 'usuarios', 'roles',
                'reportes', 'respaldos', 'perfil', 'inventario', 'cotizaciones'
            ];
            
            foreach ($allowedPrefixes as $prefix) {
                if ($request->is($prefix . '*')) {
                    return $next($request);
                }
            }
            
            return redirect()->route('dashboard');
        }

        // 🔹 VENDEDOR: Acceso a ventas, clientes, productos, cotizaciones, carrito
        if ($user->hasRole('Vendedor')) {
            // Rutas permitidas para Vendedor
            $allowedPatterns = [
                'ventas*',        // Todas las rutas de ventas (index, historial, etc.)
                'clientes*',      // Clientes
                'productos*',     // Productos
                'categorias*',    // Categorías
                'cotizaciones*',  // Cotizaciones
                'carrito*',       // Carrito
                'perfil*',        // Perfil
                'dashboard-exportar', // Exportar dashboard (si se necesita)
                'logout',         // Cerrar sesión
            ];
            
            foreach ($allowedPatterns as $pattern) {
                if ($request->is($pattern)) {
                    return $next($request);
                }
            }
            
            // Si intenta acceder a otra cosa, redirigir a ventas
            return redirect()->route('ventas.index');
        }

        // 🔹 CAJERO: Acceso a caja y ventas/historial
        if ($user->hasRole('Cajero')) {
            $allowedPatterns = [
                'caja*',          // Todas las rutas de caja
                'ventas/historial',
                'clientes*',
                'perfil*',
                'dashboard-caja',
                'reportes/caja-dashboard',
                'logout',
            ];
            
            foreach ($allowedPatterns as $pattern) {
                if ($request->is($pattern)) {
                    return $next($request);
                }
            }
            
            return redirect()->route('cajas.operaciones');
        }

        // 🔹 COBRADOR: Acceso a cobranza y clientes
        if ($user->hasRole('Cobrador')) {
            $allowedPatterns = [
                'cobranza*',
                'clientes*',
                'perfil*',
                'logout',
            ];
            
            foreach ($allowedPatterns as $pattern) {
                if ($request->is($pattern)) {
                    return $next($request);
                }
            }
            
            return redirect()->route('cobranza.index');
        }

        // 🔹 CUALQUIER OTRO ROL: Solo acceso a productos y perfil
        $allowedPatterns = ['productos*', 'perfil*', 'logout'];
        foreach ($allowedPatterns as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }
        
        // Redirigir a productos por defecto
        return redirect()->route('productos.index');
    }

    /**
     * Obtener la ruta de inicio según el rol del usuario
     */
    private function getHomeRoute($user)
    {
        $role = $user->getRoleNames()->first();
        
        $roleRoutes = [
            'Super Admin' => route('dashboard'),
            'Administrador' => route('dashboard'),
            'Vendedor' => route('ventas.index'),
            'Cajero' => route('cajas.operaciones'),
            'Cobrador' => route('cobranza.index'),
        ];
        
        if (isset($roleRoutes[$role])) {
            return $roleRoutes[$role];
        }
        
        // Cualquier otro rol no definido va a productos.index
        return route('productos.index');
    }

    /**
     * Obtener la ruta por defecto según el rol del usuario
     * Si el rol no está definido, va a productos.index
     */
    private function getDefaultRouteByRole($user)
    {
        $role = $user->getRoleNames()->first();
        
        // Si no tiene rol definido, ir a productos.index
        if (!$role) {
            return 'productos.index';
        }
        
        // Mapeo de roles conocidos a rutas por defecto
        $roleRoutes = [
            'Super Admin' => 'dashboard',
            'Administrador' => 'dashboard',
            'Vendedor' => 'ventas.index',
            'Cajero' => 'cajas.operaciones',
            'Cobrador' => 'cobranza.index',
        ];
        
        // Si el rol está en el mapa, usar su ruta
        if (isset($roleRoutes[$role])) {
            return $roleRoutes[$role];
        }
        
        // ✅ Para cualquier otro rol no definido, ir a productos.index
        return 'productos.index';
    }
}