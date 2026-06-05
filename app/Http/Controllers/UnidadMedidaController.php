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

    public function edit(UnidadMedida $unidad_medida) // 👈 Cambiado a singular
    {
        return view('unidades-medida.edit', compact('unidad_medida'));
    }

    public function update(Request $request, UnidadMedida $unidad_medida) // 👈 Cambiado a singular
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:100',
            'clave' => [
                'required',
                'string',
                'max:10',
                Rule::unique('unidad_medidas', 'clave')->ignore($unidad_medida->id),
            ],
            'nombre' => 'required|string|max:100',
            'simbolo' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'tipo.required' => 'El tipo es obligatorio.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.unique' => 'Esta clave ya está registrada en otra unidad.',
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        DB::beginTransaction();
        try {
            $unidad_medida->update([
                'tipo' => $validated['tipo'],
                'clave' => strtoupper($validated['clave']),
                'nombre' => $validated['nombre'],
                'simbolo' => $validated['simbolo'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $request->has('activo'),
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

    public function destroy(UnidadMedida $unidad_medida) // 👈 Cambiado a singular
    {
        if ($unidad_medida->insumos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar porque tiene insumos asociados.');
        }

        try {
            $unidad_medida->delete();
            return redirect()->route('unidades-medida.index')
                ->with('success', 'Unidad de medida eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar unidad: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la unidad de medida.');
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
}