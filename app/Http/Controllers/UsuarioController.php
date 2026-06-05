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
     * Obtener el ID de la sucursal activa desde la sesión
     */
    private function sucursalActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('sucursal_activa_id');
        }
        return auth()->user()->sucursal_id;
    }

    /**
     * Listado de usuarios con paginación
     */
    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $usuarios = User::with(['roles', 'sucursal', 'empresa'])
                ->where('empresa_id', $empresaId)
                ->when($sucursalId, function ($query) use ($sucursalId) {
                    return $query->where('sucursal_id', $sucursalId);
                })
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(); // Mantener filtros en paginación

            $empresaActiva = \App\Models\Empresa::find($empresaId);
            $sucursalActiva = $sucursalId ? Sucursal::find($sucursalId) : null;

            return view('usuarios.index', compact('usuarios', 'empresaActiva', 'sucursalActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar usuarios: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de usuarios. Intente nuevamente.');
        }
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        try {
            $empresaId = $this->empresaActivaId();

            $roles = Role::all();
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $empresas = auth()->user()->hasRole('Super Admin')
                ? \App\Models\Empresa::where('activo', true)->orderBy('nombre')->get()
                : collect();

            return view('usuarios.create', compact('roles', 'sucursales', 'empresas', 'empresaId'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario. Intente nuevamente.');
        }
    }

    /**
     * Almacenar nuevo usuario
     */
    public function store(Request $request)
    {
        // Validación con mensajes personalizados
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'sucursal_id' => 'nullable|integer',
            'activo' => 'boolean',
        ], [
            'name.required' => 'El nombre del usuario es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        DB::beginTransaction();
        try {
            $empresaId = auth()->user()->hasRole('Super Admin')
                ? $request->empresa_id
                : $this->empresaActivaId();

            $usuario = User::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $request->sucursal_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'activo' => $request->activo ?? true,
            ]);

            if ($request->roles) {
                $usuario->syncRoles($request->roles);
            }

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario "' . $usuario->name . '" creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear usuario: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalle de usuario
     */
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

    /**
     * Formulario de edición
     */
    public function edit(User $usuario)
    {
        try {
            $this->verificarEmpresa($usuario);

            $empresaId = $this->empresaActivaId();
            $roles = Role::all();
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $empresas = auth()->user()->hasRole('Super Admin')
                ? \App\Models\Empresa::where('activo', true)->orderBy('nombre')->get()
                : collect();

            return view('usuarios.edit', compact('usuario', 'roles', 'sucursales', 'empresas', 'empresaId'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $usuario)
    {
        $this->verificarEmpresa($usuario);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|min:6|confirmed',
            'sucursal_id' => 'nullable|integer',
            'activo' => 'boolean',
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

            // Solo Super Admin puede cambiar la empresa
            if (auth()->user()->hasRole('Super Admin') && $request->empresa_id) {
                $data['empresa_id'] = $request->empresa_id;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            $usuario->update($data);

            if ($request->roles) {
                $usuario->syncRoles($request->roles);
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

    /**
     * Eliminar usuario
     */
    public function destroy(User $usuario)
    {
        $this->verificarEmpresa($usuario);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        DB::beginTransaction();
        try {
            $nombre = $usuario->name;
            $usuario->delete();
            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario "' . $nombre . '" eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar usuario: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el usuario. Intente nuevamente.');
        }
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleActivo(User $usuario)
    {
        $this->verificarEmpresa($usuario);

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

    /**
     * Exportar usuarios a Excel
     */
    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $empresa = \App\Models\Empresa::find($empresaId);
            $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;

            // Verificar que la empresa existe
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

    /**
     * Verificar que el usuario pertenece a la empresa activa
     */
    private function verificarEmpresa(User $usuario)
    {
        if ($usuario->empresa_id !== $this->empresaActivaId()) {
            abort(403, 'Este usuario no pertenece a la empresa activa.');
        }
    }
}