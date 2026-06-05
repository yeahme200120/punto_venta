<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SucursalController extends Controller
{
    /**
     * Cambiar sucursal activa
     */
    public function cambiar(Sucursal $sucursal)
    {
        try {
            session([
                'sucursal_activa_id' => $sucursal->id,
                'sucursal_activa_nombre' => $sucursal->nombre,
            ]);

            return redirect()->back()->with('success', "Sucursal cambiada a: {$sucursal->nombre}");

        } catch (\Exception $e) {
            Log::error('Error al cambiar sucursal: ' . $e->getMessage());
            return back()->with('error', 'Error al cambiar de sucursal.');
        }
    }

    /**
     * Listar sucursales de la empresa activa
     */
    public function index(Request $request)
    {
        try {
            $empresaId = $request->empresa_id 
                ?? session('empresa_activa_id', auth()->user()->empresa_id);

            $empresa = Empresa::findOrFail($empresaId);

            $sucursales = Sucursal::withCount('usuarios')
                ->where('empresa_id', $empresaId)
                ->orderBy('nombre')
                ->paginate(10)
                ->withQueryString();

            return view('sucursales.index', compact('sucursales', 'empresa'));

        } catch (\Exception $e) {
            Log::error('Error al listar sucursales: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de sucursales.');
        }
    }

    /**
     * Formulario de creación
     */
    public function create(Request $request)
    {
        try {
            $empresaId = $request->empresa_id 
                ?? session('empresa_activa_id', auth()->user()->empresa_id);
            
            $empresa = Empresa::findOrFail($empresaId);

            if (!auth()->user()->hasRole('Super Admin') && auth()->user()->empresa_id != $empresaId) {
                abort(403, 'No tienes permiso para crear sucursales en esta empresa.');
            }

            // Si es Super Admin, mostrar todas las empresas
            $empresas = auth()->user()->hasRole('Super Admin') 
                ? Empresa::where('activo', true)->orderBy('nombre')->get() 
                : collect();

            return view('sucursales.create', compact('empresa', 'empresas'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de sucursal: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Guardar sucursal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|integer',
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
        ], [
            'empresa_id.required' => 'Debe seleccionar una empresa.',
            'nombre.required' => 'El nombre de la sucursal es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'direccion.max' => 'La dirección no debe exceder los 500 caracteres.',
            'telefono.max' => 'El teléfono no debe exceder los 20 caracteres.',
        ]);

        DB::beginTransaction();
        try {
            $sucursal = Sucursal::create([
                'empresa_id' => $validated['empresa_id'],
                'nombre' => $validated['nombre'],
                'direccion' => $validated['direccion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'activo' => true,
            ]);

            // Si es la primera sucursal de la empresa, asignarla automáticamente en sesión
            if (Sucursal::where('empresa_id', $validated['empresa_id'])->count() === 1) {
                session([
                    'sucursal_activa_id' => $sucursal->id,
                    'sucursal_activa_nombre' => $sucursal->nombre,
                ]);
            }

            DB::commit();

            return redirect()->route('empresas.show', $validated['empresa_id'])
                ->with('success', 'Sucursal "' . $validated['nombre'] . '" creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear sucursal: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear la sucursal. Intente nuevamente.');
        }
    }

    /**
     * Mostrar detalle de sucursal
     */
    public function show(Sucursal $sucursal)
    {
        try {
            $sucursal->load(['empresa', 'usuarios.roles']);
            return view('sucursales.show', compact('sucursal'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar sucursal: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos de la sucursal.');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(Sucursal $sucursal)
    {
        if (!auth()->user()->hasRole('Super Admin') && auth()->user()->empresa_id != $sucursal->empresa_id) {
            abort(403, 'No tienes permiso para editar esta sucursal.');
        }

        return view('sucursales.edit', compact('sucursal'));
    }

    /**
     * Actualizar sucursal
     */
    public function update(Request $request, Sucursal $sucursal)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre de la sucursal es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
        ]);

        DB::beginTransaction();
        try {
            $sucursal->update([
                'nombre' => $validated['nombre'],
                'direccion' => $validated['direccion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'activo' => $request->has('activo'),
            ]);

            DB::commit();

            return redirect()->route('empresas.show', $sucursal->empresa_id)
                ->with('success', 'Sucursal "' . $sucursal->nombre . '" actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar sucursal: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la sucursal.');
        }
    }

    /**
     * Eliminar sucursal
     */
    public function destroy(Sucursal $sucursal)
    {
        // Verificar que no tenga usuarios asignados
        if ($sucursal->usuarios()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: hay ' . $sucursal->usuarios()->count() . ' usuarios asignados a esta sucursal.');
        }

        DB::beginTransaction();
        try {
            $empresaId = $sucursal->empresa_id;
            $nombre = $sucursal->nombre;
            $sucursal->delete();
            DB::commit();

            return redirect()->route('empresas.show', $empresaId)
                ->with('success', 'Sucursal "' . $nombre . '" eliminada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar sucursal: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la sucursal.');
        }
    }
}