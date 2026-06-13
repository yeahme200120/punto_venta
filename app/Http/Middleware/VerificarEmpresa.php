<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VerificarEmpresa
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Verificar que tenga empresa
        if (!$user->empresa_id) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Usuario sin empresa asignada.');
        }

        // Verificar que la empresa exista
        if (!$user->empresa) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'La empresa no existe.');
        }

        // ===== NUEVO: Verificar empresa activa en sesión para Super Admin =====
        if ($user->hasRole('Super Admin')) {
            $empresaActivaId = session('empresa_activa_id');

            // Si no hay empresa en sesión, intentar usar la primera empresa disponible
            if (!$empresaActivaId) {
                $primeraEmpresa = Empresa::where('activo', true)->first();
                if ($primeraEmpresa) {
                    session([
                        'empresa_activa_id' => $primeraEmpresa->id,
                        'empresa_activa_nombre' => $primeraEmpresa->nombre
                    ]);
                    $empresaActivaId = $primeraEmpresa->id;
                }
            }

            // Verificar que la empresa activa en sesión existe y está activa
            if ($empresaActivaId) {
                $empresaActiva = Empresa::find($empresaActivaId);
                if (!$empresaActiva || !$empresaActiva->activo) {
                    session()->forget(['empresa_activa_id', 'empresa_activa_nombre']);
                }
            }
        }

        // Verificar empresa activa (la del usuario o la de sesión para Super Admin)
        $empresa = $user->hasRole('Super Admin') && session('empresa_activa_id')
            ? Empresa::find(session('empresa_activa_id'))
            : $user->empresa;

        if (!$empresa) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'No hay una empresa seleccionada o activa.');
        }

        // Verificar empresa activa
        if (!$empresa->activo) {
            if ($user->hasRole('Super Admin')) {
                session()->forget(['empresa_activa_id', 'empresa_activa_nombre']);
                return redirect()->route('dashboard')
                    ->with('error', 'La empresa seleccionada ha sido desactivada. Selecciona otra empresa.');
            }
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'La empresa ha sido desactivada. Contacte al administrador.');
        }

        // 🔥 VERIFICAR LICENCIA VIGENTE (usando el método del modelo)
        if (!$empresa->licenciaVigente()) {
            // Obtener fecha de fin para mostrar en el mensaje
            $licenciaActiva = $empresa->licenciaActiva();
            $fechaFin = $licenciaActiva->fecha_fin_periodo ?? $empresa->fecha_fin;
            $fechaFinTexto = $fechaFin ? Carbon::parse($fechaFin)->format('d/m/Y') : 'desconocida';
            
            if ($user->hasRole('Super Admin')) {
                session()->forget(['empresa_activa_id', 'empresa_activa_nombre']);
                return redirect()->route('dashboard')
                    ->with('error', "La licencia de la empresa '{$empresa->nombre}' ha expirado el {$fechaFinTexto}. Selecciona otra empresa o renueva la licencia.");
            }
            
            Auth::logout();
            return redirect()->route('login')
                ->with('error', "La licencia de la empresa ha expirado el {$fechaFinTexto}. Contacte al administrador para renovar.");
        }

        // Verificar que el usuario esté activo
        if (!$user->activo) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacte al administrador.');
        }

        return $next($request);
    }
}