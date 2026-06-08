<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = auth()->user();
        
        // Super Admin tiene todos los permisos
        if ($user && $user->hasRole('Super Admin')) {
            return $next($request);
        }
        
        // Verificar si el usuario tiene al menos uno de los permisos requeridos
        $hasPermission = false;
        $missingPermissions = [];
        
        foreach ($permissions as $permission) {
            if ($user && $user->can($permission)) {
                $hasPermission = true;
                break;
            }
            $missingPermissions[] = $permission;
        }
        
        if (!$hasPermission) {
            Log::warning('Acceso denegado a: ' . $request->path() . ' por usuario: ' . ($user ? $user->id : 'guest') . ' Permisos requeridos: ' . implode(', ', $permissions));
            
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para realizar esta acción.',
                    'required_permissions' => $permissions,
                    'missing_permissions' => $missingPermissions
                ], 403);
            }
            
            // Para sesión normal
            if ($request->route()->named()) {
                $routeName = $request->route()->getName();
                return redirect()->back()
                    ->with('error', '❌ No tienes permiso para acceder a esta sección. Permiso requerido: ' . implode(' o ', $permissions));
            }
            
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        
        return $next($request);
    }
}