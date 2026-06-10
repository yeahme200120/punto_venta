<?php

namespace App\Http\Controllers;

use App\Exports\CategoriasExport;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CategoriaController extends Controller
{
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresaActiva = \App\Models\Empresa::find($empresaId);

            $categorias = Categoria::withCount('productos')
                ->where('empresa_id', $empresaId)
                ->orderBy('nombre')
                ->paginate(10)
                ->withQueryString();

            return view('categorias.index', compact('categorias', 'empresaActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar categorías: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de categorías.');
        }
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
        ]);

        DB::beginTransaction();
        try {
            Categoria::create([
                'empresa_id' => $this->empresaActivaId(),
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => true,
            ]);

            DB::commit();

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría "' . $validated['nombre'] . '" creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear categoría: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear la categoría.');
        }
    }

    public function show(Categoria $categoria)
    {
        $categoria->load([
            'productos' => function ($q) {
                $q->orderBy('nombre');
            }
        ]);

        return view('categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
        ]);

        DB::beginTransaction();
        try {
            $categoria->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => $request->has('activo'),
            ]);

            DB::commit();

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría "' . $categoria->nombre . '" actualizada.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar categoría: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la categoría.');
        }
    }

    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = \App\Models\Empresa::find($empresaId);
            $fileName = 'categorias_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';
            return Excel::download(new CategoriasExport($empresaId), $fileName);
        } catch (\Exception $e) {
            Log::error('Error al exportar categorías: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
    public function destroy($id)
    {
        try {
            if (!auth()->user()->hasRole(['Super Admin', 'Administrador'])) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'message' => 'No tienes permisos para desactivar categorías'
                ], 403);
            }

            $categoria = Categoria::findOrFail($id);

            // Verificar si tiene productos asociados
            if ($categoria->productos_count > 0) {
                return response()->json([
                    'success' => false,
                    'icon' => 'warning',
                    'message' => 'No se puede desactivar la categoría porque tiene productos asociados'
                ], 400);
            }

            $categoria->activo = false;
            $categoria->save();

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'message' => 'Categoría desactivada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reactivar($id)
    {
        try {
            if (!auth()->user()->hasRole(['Super Admin', 'Administrador'])) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'message' => 'No tienes permisos para reactivar categorías'
                ], 403);
            }

            $categoria = Categoria::findOrFail($id);
            $categoria->activo = true;
            $categoria->save();

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'message' => 'Categoría reactivada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}