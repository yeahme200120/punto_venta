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
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'title' => 'Acceso denegado',
                    'message' => 'No tienes permiso para realizar esta acción.',
                ], 403);
            }
            
            // Mensaje flash para mostrar en el layout
            $mensaje = '🔒 No tienes permiso para acceder a esta sección.';
            $mensaje .= ' Permiso requerido: ' . str_replace('_', ' ', $missingPermission);
            
            return redirect()->route('dashboard')
                ->with('error', $mensaje);
        }
        
        return $next($request);
    }
}