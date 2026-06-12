<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = auth()->user();
        
        // Super Admin tiene todos los permisos
        if ($user && $user->hasRole('Super Admin')) {
            return $next($request);
        }
        
        if (!$user) {
            return redirect()->route('login');
        }
        
         // ✅ FORZAR RECARGA DE PERMISOS DESDE BD
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $user->refresh();
        
        // Verificar si el usuario tiene al menos uno de los permisos requeridos
        $hasPermission = false;
        $missingPermission = '';
        
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                $hasPermission = true;
                break;
            }
            $missingPermission = $permission;
        }
        
        if (!$hasPermission) {
            Log::warning('Acceso denegado: ' . $request->path() . ' - Usuario: ' . $user->email);
            
            // Obtener la ruta de redirección según el rol
            $redirectRoute = $this->getRedirectRoute($user);

            // Para peticiones AJAX (Axios) -> responder JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'title' => 'Acceso denegado',
                    'message' => 'No tienes permiso para realizar esta acción.',
                    'status' => 403
                ], 403);
            }
            
            // Para peticiones normales -> redirigir con mensaje Swal
            $mensaje = '🔒 No tienes permiso para acceder a esta sección.';
            if ($missingPermission) {
                $mensaje .= ' Permiso requerido: ' . str_replace('_', ' ', $missingPermission);
            }
            
            // ✅ Cambiar 'error' por 'swal_error' para que lo capture el componente
            return redirect()->route('dashboard')
                ->with('swal_error', $mensaje);
        }
        
        return $next($request);
    }
    private function getRedirectRoute($user)
    {
        // Verificar a qué tiene acceso
        if ($user->can('ver_ventas')) {
            return 'ventas.index';
        }
        if ($user->can('ver_productos')) {
            return 'productos.index';
        }
        if ($user->can('ver_dashboard')) {
            return 'dashboard';
        }
        
        // Fallback: logout
        return 'logout';
    }
}