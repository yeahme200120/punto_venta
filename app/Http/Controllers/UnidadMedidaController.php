<?php
// app/Http/Controllers/UnidadMedidaController.php
namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        try {
            $unidades = UnidadMedida::orderBy('tipo')->orderBy('nombre')->paginate(15);
            return view('unidades-medida.index', compact('unidades'));
        } catch (\Exception $e) {
            Log::error('Error al listar unidades: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las unidades de medida.');
        }
    }

    public function create()
    {
        return view('unidades-medida.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:100',
            'clave' => 'required|string|max:10|unique:unidad_medidas,clave',
            'nombre' => 'required|string|max:100',
            'simbolo' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'tipo.required' => 'El tipo es obligatorio.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.unique' => 'Esta clave ya está registrada.',
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        DB::beginTransaction();
        try {
            UnidadMedida::create([
                'tipo' => $validated['tipo'],
                'clave' => strtoupper($validated['clave']),
                'nombre' => $validated['nombre'],
                'simbolo' => $validated['simbolo'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $request->has('activo'),
            ]);

            DB::commit();
            return redirect()->route('unidades-medida.index')
                ->with('success', 'Unidad de medida creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear unidad: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear la unidad de medida.');
        }
    }

    public function edit(UnidadMedida $unidad) // 👈 Cambiado a singular
    {
        return view('unidades-medida.edit', compact('unidad'));
    }

    public function update(Request $request, UnidadMedida $unidad_medida) // 👈 Cambiado a singular
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:100',
            'nombre' => 'required|string|max:100',
            'simbolo' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string',
        ], [
            'tipo.required' => 'El tipo es obligatorio.',
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        DB::beginTransaction();
        try {
            $unidad_medida->update([
                'tipo' => $validated['tipo'],
                'nombre' => $validated['nombre'],
                'simbolo' => $validated['simbolo'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $request->has('activo') ? 1 : 0,
            ]);

            DB::commit();
            return redirect()->route('unidades-medida.index')
                ->with('success', 'Unidad de medida actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar unidad: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la unidad de medida.');
        }
    }

    public function desactivar($id)
    {
        try {
            $unidad = UnidadMedida::findOrFail($id);

            // Verificar permisos
            if (!auth()->user()->can('eliminar_unidades_medida') && !auth()->user()->hasRole('Super Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para desactivar esta unidad'
                ], 403);
            }

            // Verificar que no tenga insumos asociados
            if ($unidad->insumos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar porque tiene ' . $unidad->insumos()->count() . ' insumo(s) asociado(s)'
                ], 422);
            }

            $unidad->update(['activo' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Unidad "' . $unidad->nombre . '" desactivada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al desactivar unidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar la unidad'
            ], 500);
        }
    }


    public function show(UnidadMedida $unidad_medida) // 👈 Cambiado a singular
    {
        return view('unidades-medida.show', compact('unidad_medida'));
    }

    public function toggleActivo(UnidadMedida $unidad_medida) // 👈 Cambiado a singular
    {
        try {
            $unidad_medida->update(['activo' => !$unidad_medida->activo]);

            return response()->json([
                'success' => true,
                'activo' => $unidad_medida->activo,
                'message' => 'Unidad ' . ($unidad_medida->activo ? 'activada' : 'desactivada') . ' correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }
    public function reactivar($id)
    {
        try {
            $unidad = UnidadMedida::findOrFail($id);

            // Verificar permisos
            if (!auth()->user()->can('editar_unidades_medida') && !auth()->user()->hasRole('Super Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para reactivar esta unidad'
                ], 403);
            }

            $unidad->update(['activo' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Unidad "' . $unidad->nombre . '" reactivada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al reactivar unidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar la unidad'
            ], 500);
        }
    }
}