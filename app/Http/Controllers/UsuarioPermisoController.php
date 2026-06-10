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
        Log::info('Usuario actual: ' . $currentUser->email . ' (ID: ' . $currentUser->id . ')');

        if ($usuario->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
            Log::warning('Intento de editar Super Admin por usuario no autorizado: ' . $currentUser->email);
            abort(403, 'No tienes permiso para editar los permisos de un Super Administrador.');
        }

        $todosPermisos = Permission::all();
        Log::info('Total permisos disponibles: ' . $todosPermisos->count());
        
        if (!$currentUser->hasRole('Super Admin')) {
            $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
            $todosPermisos = $todosPermisos->filter(fn($p) => !str_contains($p->name, implode('|', $excluirModulos)));
            Log::info('Permisos después de filtrar (no Super Admin): ' . $todosPermisos->count());
        }

        $roles = Role::all();
        if (!$currentUser->hasRole('Super Admin')) {
            $roles = $roles->filter(fn($r) => $r->name !== 'Super Admin');
        }
        Log::info('Roles disponibles: ' . $roles->pluck('name')->implode(', '));

        $permisosUsuario = $usuario->getAllPermissions()->pluck('name')->toArray();
        Log::info('Permisos actuales del usuario: ' . json_encode($permisosUsuario));

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
                if (str_contains($permiso->name, $clave))
                    return $modulo;
            }
            return 'Otros';
        })->sortKeys();

        return view('usuarios.permisos', compact('usuario', 'roles', 'permisosAgrupados', 'permisosUsuario'));
    }

    // Obtener permisos de un rol (para Axios)
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
            $permisosAntes = $usuario->getAllPermissions()->pluck('name')->toArray();
            Log::info('Permisos ANTES de la actualización: ' . json_encode($permisosAntes));
            
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

            // Filtrar permisos asignables
            $permisosAsignar = $request->permisos ?? [];
            Log::info('Permisos a asignar: ' . json_encode($permisosAsignar));
            Log::info('Cantidad de permisos a asignar: ' . count($permisosAsignar));

            // ASIGNAR PERMISOS
            $usuario->syncPermissions($permisosAsignar);
            Log::info('syncPermissions ejecutado');

            // Verificar permisos DESPUÉS de la asignación
            $permisosDespues = $usuario->getAllPermissions()->pluck('name')->toArray();
            Log::info('Permisos DESPUÉS de syncPermissions (sin refrescar): ' . json_encode($permisosDespues));

            // FORZAR limpieza de caché de permisos
            Log::info('Limpiando caché de permisos...');
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            Cache::forget('spatie.permission.cache');
            Log::info('Caché de permisos limpiada');

            // Refrescar el modelo para obtener datos actualizados
            $usuario->refresh();
            Log::info('Modelo refrescado');

            // Verificar permisos después de limpiar caché
            $permisosFinal = $usuario->getAllPermissions()->pluck('name')->toArray();
            Log::info('Permisos FINALES después de limpiar caché: ' . json_encode($permisosFinal));

            // Verificar rol después
            $rolDespues = $usuario->roles->first();
            Log::info('Rol DESPUÉS de la actualización: ' . ($rolDespues ? $rolDespues->name : 'ninguno'));

            // Comparar cambios
            if ($permisosAntes == $permisosFinal) {
                Log::warning('⚠️ LOS PERMISOS NO CAMBIARON! Antes: ' . json_encode($permisosAntes) . ' - Después: ' . json_encode($permisosFinal));
            } else {
                Log::info('✅ PERMISOS ACTUALIZADOS CORRECTAMENTE');
                Log::info('Permisos removidos: ' . json_encode(array_diff($permisosAntes, $permisosFinal)));
                Log::info('Permisos agregados: ' . json_encode(array_diff($permisosFinal, $permisosAntes)));
            }

            // Expulsar usuarios afectados
            if ($rolAnterior && $rolAnterior->name !== $rolSeleccionado) {
                Log::info('Expulsando usuarios con rol anterior: ' . $rolAnterior->name);
                $this->expulsarUsuariosConRol($rolAnterior->name);
            }
            if ($rolSeleccionado) {
                Log::info('Expulsando usuarios con nuevo rol: ' . $rolSeleccionado);
                $this->expulsarUsuariosConRol($rolSeleccionado);
            }

            // También expulsar al usuario actual si no es el mismo
            if (auth()->id() !== $usuario->id) {
                Log::info('Expulsando usuario editado (no es el actual): ' . $usuario->email);
                $this->expulsarUsuario($usuario->id);
            }

            // Recargar el usuario actual
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

    private function expulsarUsuariosConRol($roleName)
    {
        Log::info('Expulsando usuarios con rol: ' . $roleName);
        $users = User::role($roleName)->get();
        Log::info('Usuarios encontrados con rol ' . $roleName . ': ' . $users->count());
        
        foreach ($users as $user) {
            Log::info('Expulsando usuario: ' . $user->email . ' (ID: ' . $user->id . ')');
            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        }
        
        // Limpiar caché de permisos para forzar recarga
        Cache::forget('spatie.permission.cache');
        Log::info('Caché de permisos limpiada después de expulsar usuarios');
    }
}