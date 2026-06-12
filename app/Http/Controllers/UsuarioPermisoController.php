<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsuarioPermisoController extends Controller
{
    public function edit(User $usuario)
    {
        Log::info('=== EDIT PERMISOS ===');
        Log::info('Usuario a editar: ' . $usuario->email . ' (ID: ' . $usuario->id . ')');
        
        $currentUser = auth()->user();

        if ($usuario->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
            abort(403, 'No tienes permiso para editar los permisos de un Super Administrador.');
        }

        // Todos los permisos disponibles (filtrados por rol del usuario actual)
        $todosPermisos = Permission::all();
        if (!$currentUser->hasRole('Super Admin')) {
            $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
            $todosPermisos = $todosPermisos->filter(fn($p) => !str_contains($p->name, implode('|', $excluirModulos)));
        }

        // Roles disponibles (filtrados)
        $roles = Role::all();
        if (!$currentUser->hasRole('Super Admin')) {
            $roles = $roles->filter(fn($r) => $r->name !== 'Super Admin');
        }

        // Permisos DIRECTOS del usuario (los que se pueden modificar)
        $permisosDirectos = $usuario->getDirectPermissions()->pluck('name')->toArray();

        // Rol actual y sus permisos (solo lectura)
        $rolActual = $usuario->roles->first();
        $permisosDelRol = $rolActual ? $rolActual->permissions->pluck('name')->toArray() : [];

        // Agrupar todos los permisos por módulo para mostrarlos en la UI
        $permisosAgrupados = $todosPermisos->groupBy(function ($permiso) {
            $mapa = [
                'dashboard' => 'Dashboard',
                'empresas' => 'Empresas',
                'licencias' => 'Licencias',
                'inventario' => 'Inventario',
                'compras' => 'Compras',
                'proveedores' => 'Proveedores',
                'ventas' => 'Ventas',
                'facturacion' => 'Facturacion',
                'clientes' => 'Clientes',
                'cobranza' => 'Cobranza',
                'caja' => 'Caja',
                'formaspago' => 'FormasPago',
                'notificaciones' => 'Notificaciones',
                'impresoras' => 'Impresoras',
                'ticket' => 'Ticket',
                'usuarios' => 'Usuarios',
                'roles' => 'Roles',
                'reportes' => 'Reportes',
                'respaldos' => 'Respaldos',
                'insumos' => 'Insumos',
                'unidades_medida' => 'UnidadesMedida',
            ];
            foreach ($mapa as $clave => $modulo) {
                if (str_contains($permiso->name, $clave)) return $modulo;
            }
            return 'Otros';
        })->sortKeys();

        return view('usuarios.permisos', compact(
            'usuario',
            'roles',
            'permisosAgrupados',
            'permisosDirectos',
            'permisosDelRol',
            'rolActual'
        ));
    }

    public function getRolePermissions($roleName)
    {
        Log::info('=== GET ROLE PERMISSIONS ===');
        Log::info('Rol solicitado: ' . $roleName);
        
        try {
            $role = Role::findByName($roleName);
            $permisos = $role->permissions->pluck('name');
            Log::info('Permisos del rol ' . $roleName . ': ' . json_encode($permisos));
            
            return response()->json([
                'success' => true,
                'permisos' => $permisos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar permisos del rol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar permisos del rol: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, User $usuario)
    {
        Log::info('=== UPDATE PERMISOS ===');
        Log::info('Usuario a actualizar: ' . $usuario->email . ' (ID: ' . $usuario->id . ')');
        Log::info('Datos recibidos del formulario:');
        Log::info(' - roles: ' . json_encode($request->roles));
        Log::info(' - permisos: ' . json_encode($request->permisos));

        try {
            $currentUser = auth()->user();
            Log::info('Usuario que realiza la acción: ' . $currentUser->email . ' (ID: ' . $currentUser->id . ')');

            // Verificar que no se edite un Super Admin
            if ($usuario->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
                Log::warning('Intento de modificar Super Admin por usuario no autorizado: ' . $currentUser->email);
                return redirect()->route('usuarios.index')
                    ->with('swal_error', 'No tienes permiso para modificar los permisos de un Super Administrador.');
            }

            $rolSeleccionado = $request->roles ? (is_array($request->roles) ? $request->roles[0] : $request->roles) : null;
            Log::info('Rol seleccionado: ' . ($rolSeleccionado ?? 'ninguno'));

            if (!$currentUser->hasRole('Super Admin') && $rolSeleccionado === 'Super Admin') {
                Log::warning('Intento de asignar rol Super Admin por usuario no autorizado: ' . $currentUser->email);
                return redirect()->route('usuarios.index')
                    ->with('swal_error', 'No tienes permiso para asignar el rol Super Administrador.');
            }

            // Guardar permisos ANTES de modificar
            $permisosAntes = $usuario->getDirectPermissions()->pluck('name')->toArray();
            Log::info('Permisos DIRECTOS ANTES de la actualización: ' . json_encode($permisosAntes));

            $rolAntes = $usuario->roles->first();
            Log::info('Rol ANTES de la actualización: ' . ($rolAntes ? $rolAntes->name : 'ninguno'));

            // Guardar el rol anterior
            $rolAnterior = $usuario->roles->first();

            // Sincronizar rol
            if ($rolSeleccionado) {
                Log::info('Asignando rol: ' . $rolSeleccionado);
                $usuario->syncRoles([$rolSeleccionado]);
            } else {
                Log::info('Eliminando todos los roles');
                $usuario->syncRoles([]);
            }

            // PERMISOS DIRECTOS: solo los que vienen del formulario
            $permisosDirectos = $request->permisos ?? [];
            Log::info('Permisos directos a asignar: ' . json_encode($permisosDirectos));
            Log::info('Cantidad de permisos directos a asignar: ' . count($permisosDirectos));

            // ASIGNAR PERMISOS DIRECTOS (reemplaza los anteriores)
            $usuario->syncPermissions($permisosDirectos);
            Log::info('syncPermissions ejecutado');

            // Verificar permisos DESPUÉS de la asignación
            $permisosDespues = $usuario->getDirectPermissions()->pluck('name')->toArray();
            Log::info('Permisos DIRECTOS DESPUÉS de syncPermissions (sin refrescar): ' . json_encode($permisosDespues));

            // FORZAR limpieza de caché de permisos
            Log::info('Limpiando caché de permisos...');
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            Cache::forget('spatie.permission.cache');
            Log::info('Caché de permisos limpiada');

            // Refrescar el modelo para obtener datos actualizados
            $usuario->refresh();
            Log::info('Modelo refrescado');

            // Verificar permisos directos después de limpiar caché
            $permisosFinal = $usuario->getDirectPermissions()->pluck('name')->toArray();
            Log::info('Permisos DIRECTOS FINALES después de limpiar caché: ' . json_encode($permisosFinal));

            // Verificar rol después
            $rolDespues = $usuario->roles->first();
            Log::info('Rol DESPUÉS de la actualización: ' . ($rolDespues ? $rolDespues->name : 'ninguno'));

            // Comparar cambios
            if ($permisosAntes == $permisosFinal) {
                Log::warning('⚠️ LOS PERMISOS DIRECTOS NO CAMBIARON! Antes: ' . json_encode($permisosAntes) . ' - Después: ' . json_encode($permisosFinal));
            } else {
                Log::info('✅ PERMISOS DIRECTOS ACTUALIZADOS CORRECTAMENTE');
                Log::info('Permisos removidos: ' . json_encode(array_diff($permisosAntes, $permisosFinal)));
                Log::info('Permisos agregados: ' . json_encode(array_diff($permisosFinal, $permisosAntes)));
            }

            // ⚠️ SOLO EXPULSAR AL USUARIO EDITADO (NO A TODOS LOS DEL ROL)
            $this->expulsarUsuario($usuario->id);

            // Recargar el usuario actual si es el mismo
            if (auth()->id() === $usuario->id) {
                Log::info('El usuario actual fue modificado, cerrando sesión');
                auth()->logout();
                return redirect()->route('login')
                    ->with('swal_info', 'Tus permisos han sido actualizados. Por favor, inicia sesión nuevamente.');
            }

            Log::info('✅ Proceso completado exitosamente');
            return redirect()->route('usuarios.index')
                ->with('swal_success', 'Permisos actualizados correctamente. El usuario deberá volver a iniciar sesión.');

        } catch (\Exception $e) {
            Log::error('❌ ERROR en update: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->route('usuarios.index')
                ->with('swal_error', 'Error al guardar los permisos: ' . $e->getMessage());
        }
    }

    /**
     * Expulsar a un usuario específico (eliminar sus sesiones activas)
     */
    private function expulsarUsuario($userId)
    {
        Log::info('Expulsando usuario ID: ' . $userId);
        if (config('session.driver') === 'database') {
            $deleted = DB::table('sessions')->where('user_id', $userId)->delete();
            Log::info('Sesiones eliminadas para usuario ' . $userId . ': ' . $deleted);
        } else {
            Log::info('Driver de sesión no es database, no se puede expulsar automáticamente');
        }
    }
}