<?php

namespace App\Http\Controllers;

use App\Exports\RolesExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    /**
     * Listado de roles - Ocultar Super Admin para no Super Admin
     */
    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresaActiva = \App\Models\Empresa::find($empresaId);
            $currentUser = auth()->user();

            $query = Role::withCount('users')
                ->with([
                    'permissions',
                    'users' => function ($query) use ($empresaId) {
                        $query->where('empresa_id', $empresaId);
                    }
                ]);

            // Si NO es Super Admin, excluir el rol Super Admin
            if (!$currentUser->hasRole('Super Admin')) {
                $query->where('name', '!=', 'Super Admin');
            }

            $roles = $query->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            return view('roles.index', compact('roles', 'empresaActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar roles: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de roles.');
        }
    }

    /**
     * Formulario de creación - No permitir crear rol Super Admin para no Super Admin
     */
    public function create()
    {
        try {
            $currentUser = auth()->user();

            // Si no es Super Admin, no puede crear el rol Super Admin
            $permisos = Permission::all();

            if (!$currentUser->hasRole('Super Admin')) {
                // Excluir permisos de módulos restringidos
                $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
                $permisos = $permisos->filter(function ($permiso) use ($excluirModulos) {
                    foreach ($excluirModulos as $modulo) {
                        if (str_contains($permiso->name, $modulo)) {
                            return false;
                        }
                    }
                    return true;
                });
            }

            $permisos = $permisos->groupBy(function ($permiso) {
                $partes = explode('_', $permiso->name);
                array_shift($partes);
                return implode('_', $partes) ?: 'otros';
            })->sortKeys();

            return view('roles.create', compact('permisos'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de rol: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Almacenar nuevo rol - No permitir crear Super Admin
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        // Validar que no se pueda crear el rol Super Admin si no es Super Admin
        if (!$currentUser->hasRole('Super Admin') && $request->name === 'Super Admin') {
            return back()->withInput()->with('error', 'No tienes permiso para crear el rol Super Administrador.');
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con este nombre.',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $validated['name']]);

            if ($request->permisos) {
                $role->syncPermissions($request->permisos);
            }

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Rol "' . $role->name . '" creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear rol: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el rol. Intente nuevamente.');
        }
    }

    /**
     * Mostrar detalle de rol - No permitir ver Super Admin
     */
    public function show(Role $role)
    {
        try {
            $currentUser = auth()->user();

            // Si no es Super Admin y el rol es Super Admin, denegar acceso
            if (!$currentUser->hasRole('Super Admin') && $role->name === 'Super Admin') {
                abort(403, 'No tienes permiso para ver el rol Super Administrador.');
            }

            $empresaId = $this->empresaActivaId();

            $role->load([
                'permissions',
                'users' => function ($query) use ($empresaId) {
                    $query->where('empresa_id', $empresaId)->with('sucursal');
                }
            ]);

            $permisosAgrupados = $role->permissions->groupBy(function ($permiso) {
                $partes = explode('_', $permiso->name);
                array_shift($partes);
                return implode('_', $partes) ?: 'otros';
            })->sortKeys();

            return view('roles.show', compact('role', 'permisosAgrupados'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar rol: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos del rol.');
        }
    }

    /**
     * Formulario de edición - No permitir editar Super Admin
     */
    public function edit(Role $role)
    {
        try {
            $currentUser = auth()->user();

            // Si no es Super Admin y el rol es Super Admin, denegar acceso
            if (!$currentUser->hasRole('Super Admin') && $role->name === 'Super Admin') {
                abort(403, 'No tienes permiso para editar el rol Super Administrador.');
            }

            $permisos = Permission::all();

            if (!$currentUser->hasRole('Super Admin')) {
                // Excluir permisos de módulos restringidos
                $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
                $permisos = $permisos->filter(function ($permiso) use ($excluirModulos) {
                    foreach ($excluirModulos as $modulo) {
                        if (str_contains($permiso->name, $modulo)) {
                            return false;
                        }
                    }
                    return true;
                });
            }

            $permisos = $permisos->groupBy(function ($permiso) {
                $partes = explode('_', $permiso->name);
                array_shift($partes);
                return implode('_', $partes) ?: 'otros';
            })->sortKeys();

            return view('roles.edit', compact('role', 'permisos'));

        } catch (\Exception $e) {
            Log::error('Error al cargar edición de rol: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualizar rol - No permitir modificar Super Admin
     */
    public function update(Request $request, Role $role)
    {
        $currentUser = auth()->user();

        // Si no es Super Admin y el rol es Super Admin, denegar
        if (!$currentUser->hasRole('Super Admin') && $role->name === 'Super Admin') {
            return back()->with('error', 'No tienes permiso para modificar el rol Super Administrador.');
        }

        // Validar que no se intente cambiar el nombre a Super Admin
        if (!$currentUser->hasRole('Super Admin') && $request->name === 'Super Admin') {
            return back()->withInput()->with('error', 'No tienes permiso para crear el rol Super Administrador.');
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con este nombre.',
        ]);

        DB::beginTransaction();
        try {
            $role->update(['name' => $validated['name']]);
            $role->syncPermissions($request->permisos ?? []);
            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Rol "' . $role->name . '" actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar rol: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el rol. Intente nuevamente.');
        }
    }

    /**
     * Eliminar rol - No permitir eliminar Super Admin
     */
    public function destroy(Role $role)
    {
        $currentUser = auth()->user();

        if ($role->name === 'Super Admin') {
            return back()->with('error', 'No se puede eliminar el rol Super Administrador.');
        }

        // Si no es Super Admin, no puede eliminar ningún rol (opcional)
        if (!$currentUser->hasRole('Super Admin')) {
            return back()->with('error', 'No tienes permiso para eliminar roles.');
        }

        DB::beginTransaction();
        try {
            $nombre = $role->name;
            $role->delete();
            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Rol "' . $nombre . '" eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar rol: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el rol. Intente nuevamente.');
        }
    }

    /**
     * Exportar roles a Excel
     */
    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = \App\Models\Empresa::find($empresaId);

            if (!$empresa) {
                return back()->with('error', 'No se encontró la empresa activa.');
            }

            $fileName = 'roles_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new RolesExport($empresaId), $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar roles: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
    public function editPermisos(Role $role)
    {
        try {
            $currentUser = auth()->user();

            // No se puede editar Super Admin si no eres Super Admin
            if ($role->name === 'Super Admin' && !$currentUser->hasRole('Super Admin')) {
                abort(403, 'No tienes permiso para editar los permisos del rol Super Administrador.');
            }

            // Obtener todos los permisos (filtrados por rol del usuario actual)
            $todosPermisos = Permission::all();

            if (!$currentUser->hasRole('Super Admin')) {
                $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
                $todosPermisos = $todosPermisos->filter(fn($p) => !str_contains($p->name, implode('|', $excluirModulos)));
            }

            // Permisos actuales del rol
            $permisosDelRol = $role->permissions->pluck('name')->toArray();

            // Agrupar permisos por módulo
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

            return view('roles.permisos', compact('role', 'permisosAgrupados', 'permisosDelRol'));

        } catch (\Exception $e) {
            Log::error('Error al cargar permisos del rol: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los permisos del rol.');
        }
    }

    /**
     * Actualizar permisos del rol - Expulsa a todos los usuarios con ese rol
     */
    public function updatePermisos(Request $request, Role $role)
    {
        Log::info('=== INICIO updatePermisos ===');
        Log::info('Rol ID: ' . $role->id);
        Log::info('Rol nombre: ' . $role->name);
        Log::info('Permisos recibidos: ' . json_encode($request->permisos));

        try {
            $currentUser = auth()->user();
            Log::info('Usuario actual: ' . ($currentUser ? $currentUser->email : 'No autenticado'));

            // Verificar permisos para editar Super Admin
            if ($role->name === 'Super Admin' && !$currentUser->hasRole('Super Admin')) {
                Log::warning('Intento de editar Super Admin sin permisos');
                return redirect()->route('roles.index')
                    ->with('swal_error', 'No tienes permiso para modificar los permisos del rol Super Administrador.');
            }

            // Obtener permisos a asignar
            $permisosAsignar = $request->permisos ?? [];
            Log::info('Permisos a asignar (sin filtrar): ' . json_encode($permisosAsignar));

            // Filtrar permisos no permitidos (si no es Super Admin)
            if (!$currentUser->hasRole('Super Admin')) {
                $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
                $permisosAsignar = array_filter($permisosAsignar, function ($permiso) use ($excluirModulos) {
                    foreach ($excluirModulos as $modulo) {
                        if (str_contains($permiso, $modulo))
                            return false;
                    }
                    return true;
                });
                Log::info('Permisos a asignar (después de filtrar): ' . json_encode($permisosAsignar));
            }

            // Guardar nombre del rol para mensaje
            $nombreRol = $role->name;

            // Asignar permisos al rol
            Log::info('Asignando permisos al rol...');
            $role->syncPermissions($permisosAsignar);
            Log::info('Permisos asignados correctamente');

            // Limpiar caché de permisos
            Log::info('Limpiando caché de permisos...');
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            Cache::forget('spatie.permission.cache');
            Log::info('Caché limpiada');

            // 🔥 EXPULSAR A TODOS LOS USUARIOS QUE TIENEN ESTE ROL
            Log::info('Buscando usuarios con rol: ' . $nombreRol);
            $users = User::role($nombreRol)->get();
            Log::info('Usuarios encontrados: ' . $users->count());
            $expulsados = 0;

            foreach ($users as $user) {
                Log::info('Procesando usuario: ' . $user->email . ' (ID: ' . $user->id . ')');
                if (config('session.driver') === 'database') {
                    $deleted = DB::table('sessions')->where('user_id', $user->id)->delete();
                    Log::info('Sesiones eliminadas para usuario ' . $user->id . ': ' . $deleted);
                    $expulsados++;
                } else {
                    Log::info('Driver de sesión no es database, no se puede expulsar');
                }
            }

            // Si el usuario actual tiene este rol, cerrar su sesión también
            if (auth()->user()->hasRole($nombreRol)) {
                Log::info('El usuario actual tiene el rol, cerrando sesión');
                auth()->logout();

                return redirect()->route('login')
                    ->with('swal_info', "Los permisos del rol '{$nombreRol}' han cambiado. Por favor, inicia sesión nuevamente. ({$expulsados} usuarios afectados)");
            }

            $mensaje = "Permisos del rol '{$nombreRol}' actualizados correctamente.";
            if ($expulsados > 0) {
                $mensaje .= " Se han cerrado {$expulsados} sesiones activas de usuarios con este rol.";
            }

            Log::info('Redirigiendo con mensaje de éxito');
            return redirect()->route('roles.permisos_rol.edit', $role)
                ->with('swal_success', $mensaje);

        } catch (\Exception $e) {
            Log::error('❌ ERROR en updatePermisos: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('roles.index')
                ->with('swal_error', 'Error al actualizar los permisos: ' . $e->getMessage());
        }
    }
}