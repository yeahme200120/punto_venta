<?php

namespace App\Http\Controllers;

use App\Exports\RolesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Obtener el ID de la empresa activa desde la sesión
     */
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    /**
     * Listado de roles
     */
    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresaActiva = \App\Models\Empresa::find($empresaId);

            $roles = Role::withCount('users')
                ->with(['permissions', 'users' => function ($query) use ($empresaId) {
                    $query->where('empresa_id', $empresaId);
                }])
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            return view('roles.index', compact('roles', 'empresaActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar roles: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de roles. Intente nuevamente.');
        }
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        try {
            $permisos = Permission::all()->groupBy(function ($permiso) {
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
     * Almacenar nuevo rol
     */
    public function store(Request $request)
    {
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
     * Mostrar detalle de rol
     */
    public function show(Role $role)
    {
        try {
            $empresaId = $this->empresaActivaId();

            $role->load(['permissions', 'users' => function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId)->with('sucursal');
            }]);

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
     * Formulario de edición
     */
    public function edit(Role $role)
    {
        try {
            $permisos = Permission::all()->groupBy(function ($permiso) {
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
     * Actualizar rol
     */
    public function update(Request $request, Role $role)
    {
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
     * Eliminar rol
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'No se puede eliminar el rol Super Admin.');
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
            return back()->with('error', 'Error al generar el archivo Excel. Intente nuevamente.');
        }
    }
}