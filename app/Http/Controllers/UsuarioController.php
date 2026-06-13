<?php

namespace App\Http\Controllers;

use App\Exports\UsuariosExport;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UsuarioController extends Controller
{
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    private function sucursalActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('sucursal_activa_id');
        }
        return auth()->user()->sucursal_id;
    }

    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();
            $user = auth()->user();
            
            $query = User::with(['roles', 'sucursal', 'empresa'])
                ->where('empresa_id', $empresaId);
            
            // Si NO es Super Admin, excluir al Super Admin de la lista
            if (!$user->hasRole('Super Admin')) {
                $query->whereDoesntHave('roles', function($q) {
                    $q->where('name', 'Super Admin');
                });
            }
            
            $usuarios = $query->when($sucursalId, function ($query) use ($sucursalId) {
                    return $query->where('sucursal_id', $sucursalId);
                })
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            $empresaActiva = \App\Models\Empresa::find($empresaId);
            $sucursalActiva = $sucursalId ? Sucursal::find($sucursalId) : null;

            return view('usuarios.index', compact('usuarios', 'empresaActiva', 'sucursalActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar usuarios: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de usuarios. Intente nuevamente.');
        }
    }

    public function create()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $user = auth()->user();
            
            // Si NO es Super Admin, excluir el rol Super Admin de la lista
            if ($user->hasRole('Super Admin')) {
                $roles = Role::all();
            } else {
                $roles = Role::where('name', '!=', 'Super Admin')->get();
            }
            
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $empresas = $user->hasRole('Super Admin')
                ? \App\Models\Empresa::where('activo', true)->orderBy('nombre')->get()
                : collect();

            return view('usuarios.create', compact('roles', 'sucursales', 'empresas', 'empresaId'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario. Intente nuevamente.');
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'sucursal_id' => 'nullable|exists:sucursals,id',
            'activo' => 'boolean',
            'roles' => 'array',
        ], [
            'name.required' => 'El nombre del usuario es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'sucursal_id' => $request->sucursal_id,
                'activo' => $request->has('activo'),
                'empresa_id' => $this->empresaActivaId(),
            ];

            // Solo Super Admin puede asignar empresa_id
            if ($user->hasRole('Super Admin') && $request->empresa_id) {
                $data['empresa_id'] = $request->empresa_id;
            }

            $usuario = User::create($data);

            // Asignar roles
            if ($request->has('roles')) {
                $rolesAsignar = $request->roles;
                if (!$user->hasRole('Super Admin')) {
                    $rolesAsignar = array_diff($rolesAsignar, ['Super Admin']);
                }
                if (!empty($rolesAsignar)) {
                    $usuario->syncRoles($rolesAsignar);
                }
            }

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario "' . $usuario->name . '" creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear usuario: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el usuario. Intente nuevamente.');
        }
    }

    public function edit(User $usuario)
    {
        try {
            $this->verificarEmpresa($usuario);
            $this->verificarPermisoEdicion($usuario);

            $empresaId = $this->empresaActivaId();
            $user = auth()->user();
            
            // Si NO es Super Admin, excluir el rol Super Admin
            if ($user->hasRole('Super Admin')) {
                $roles = Role::all();
            } else {
                $roles = Role::where('name', '!=', 'Super Admin')->get();
            }
            
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $empresas = $user->hasRole('Super Admin')
                ? \App\Models\Empresa::where('activo', true)->orderBy('nombre')->get()
                : collect();

            return view('usuarios.edit', compact('usuario', 'roles', 'sucursales', 'empresas', 'empresaId'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    public function update(Request $request, User $usuario)
    {
        $this->verificarEmpresa($usuario);
        $this->verificarPermisoEdicion($usuario);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|min:6|confirmed',
            'sucursal_id' => 'nullable|exists:sucursals,id',
            'activo' => 'boolean',
            'roles' => 'array',
        ], [
            'name.required' => 'El nombre del usuario es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las nuevas contraseñas no coinciden.',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'sucursal_id' => $request->sucursal_id,
                'activo' => $request->has('activo'),
            ];

            if (auth()->user()->hasRole('Super Admin') && $request->empresa_id) {
                $data['empresa_id'] = $request->empresa_id;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            $usuario->update($data);

            // Validar que no se asigne el rol Super Admin si el usuario actual no es Super Admin
            if ($request->has('roles')) {
                $rolesAsignar = $request->roles;
                if (!auth()->user()->hasRole('Super Admin')) {
                    $rolesAsignar = array_diff($rolesAsignar, ['Super Admin']);
                }
                if (!empty($rolesAsignar)) {
                    $usuario->syncRoles($rolesAsignar);
                } else {
                    $usuario->syncRoles([]);
                }
            }

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario "' . $usuario->name . '" actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el usuario. Intente nuevamente.');
        }
    }

    public function destroy(User $usuario)
    {
        $this->verificarEmpresa($usuario);
        $this->verificarPermisoEdicion($usuario);

        if ($usuario->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propio usuario.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $nombre = $usuario->name;
            $usuario->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario "' . $nombre . '" eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario. Intente nuevamente.'
            ], 500);
        }
    }

    public function show(User $usuario)
    {
        try {
            $this->verificarEmpresa($usuario);
            $usuario->load(['roles', 'sucursal', 'empresa']);
            return view('usuarios.show', compact('usuario'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar usuario: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos del usuario.');
        }
    }

    public function toggleActivo(User $usuario)
    {
        $this->verificarEmpresa($usuario);
        $this->verificarPermisoEdicion($usuario);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        DB::beginTransaction();
        try {
            $usuario->update(['activo' => !$usuario->activo]);
            DB::commit();

            $estado = $usuario->activo ? 'activado' : 'desactivado';
            return back()->with('success', 'Usuario "' . $usuario->name . '" ' . $estado . ' correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de usuario: ' . $e->getMessage());
            return back()->with('error', 'Error al cambiar el estado del usuario.');
        }
    }

    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $empresa = \App\Models\Empresa::find($empresaId);
            $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;

            if (!$empresa) {
                return back()->with('error', 'No se encontró la empresa activa.');
            }

            $fileName = 'usuarios_' . str_replace(' ', '_', $empresa->nombre);
            if ($sucursal) {
                $fileName .= '_' . str_replace(' ', '_', $sucursal->nombre);
            }
            $fileName .= '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new UsuariosExport($empresaId, $sucursalId),
                $fileName
            );

        } catch (\Exception $e) {
            Log::error('Error al exportar usuarios: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel. Intente nuevamente.');
        }
    }

    public function perfil()
    {
        $usuario = auth()->user();
        return view('usuarios.perfil', compact('usuario'));
    }

    public function updatePerfil(Request $request)
    {
        $usuario = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        try {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            $usuario->update($data);

            return back()->with('success', 'Perfil actualizado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar perfil: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar el perfil.');
        }
    }

    private function verificarEmpresa(User $usuario)
    {
        if ($usuario->empresa_id !== $this->empresaActivaId()) {
            abort(403, 'Este usuario no pertenece a la empresa activa.');
        }
    }

    /**
     * Verificar que el usuario actual pueda editar al usuario destino
     * Un Administrador NO puede editar a un Super Admin
     */
    private function verificarPermisoEdicion(User $usuario)
    {
        $currentUser = auth()->user();
        
        // Si el usuario a editar tiene rol Super Admin y el usuario actual NO es Super Admin
        if ($usuario->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
            abort(403, 'No tienes permiso para editar un usuario Super Administrador.');
        }
    }
}