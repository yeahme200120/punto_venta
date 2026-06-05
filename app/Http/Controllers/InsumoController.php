<?php

namespace App\Http\Controllers;

use App\Exports\InsumosExport;
use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\InventarioMovimiento;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class InsumoController extends Controller
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
            $insumos = Insumo::with(['proveedor', 'productos', 'unidadMedida'])
                ->where('empresa_id', $empresaId)
                ->orderBy('nombre')
                ->paginate(10);

            $insumos->each(function ($i) {
                $i->stock_bajo = $i->stock <= $i->stock_minimo;
            });

            return view('insumos.index', compact('insumos'));
        } catch (\Exception $e) {
            Log::error('Error al listar insumos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar insumos.');
        }
    }

    public function create()
    {
        $proveedores = Proveedor::where('empresa_id', $this->empresaActivaId())
            ->where('activo', true)->orderBy('nombre')->get();
        $unidadesMedida = UnidadMedida::where('activo', true)
            ->orderBy('nombre')
            ->get();
        $codigo = Insumo::generarCodigo();
        return view('insumos.create', compact('proveedores', 'unidadesMedida', 'codigo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id' => 'nullable|exists:proveedors,id',
            'unidad_medida_id' => 'nullable|exists:unidad_medidas,id',
            'codigo' => 'nullable|string|max:20|unique:insumos,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo_unitario' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'required|numeric|min:0|gt:stock_minimo',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre del insumo es obligatorio.',
            'costo_unitario.required' => 'El costo unitario es obligatorio.',
            'stock.required' => 'El stock inicial es obligatorio.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'stock_maximo.required' => 'El stock máximo es obligatorio.',
            'stock_maximo.gt' => 'El stock máximo debe ser mayor que el stock mínimo.',
            'codigo.unique' => 'Este código ya está registrado.',
        ]);

        DB::beginTransaction();
        try {
            $insumo = Insumo::create([
                'empresa_id' => $this->empresaActivaId(),
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'codigo' => $validated['codigo'] ?? null,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'unidad_medida_id' => $validated['unidad_medida_id'] ?? null,
                'costo_unitario' => $validated['costo_unitario'],
                'stock' => $validated['stock'],
                'stock_minimo' => $validated['stock_minimo'],
                'stock_maximo' => $validated['stock_maximo'],
                'activo' => true,
            ]);

            if ($validated['stock'] > 0) {
                InventarioMovimiento::create([
                    'empresa_id' => $this->empresaActivaId(),
                    'insumo_id' => $insumo->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'entrada',
                    'motivo' => 'ajuste_inventario',
                    'cantidad' => $validated['stock'],
                    'costo_unitario' => $validated['costo_unitario'],
                    'costo_total' => $validated['stock'] * $validated['costo_unitario'],
                    'observacion' => 'Stock inicial al crear insumo',
                ]);
            }

            DB::commit();
            return redirect()->route('insumos.index')->with('success', 'Insumo creado.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear insumo: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear el insumo.');
        }
    }

    public function show(Insumo $insumo)
    {
        $insumo->load([
            'proveedor',
            'productos',
            'movimientos' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(20);
            }
        ]);
        return view('insumos.show', compact('insumo'));
    }

    public function edit(Insumo $insumo)
    {
        $this->verificarEmpresa($insumo);

        $empresaId = $this->empresaActivaId();

        $proveedores = Proveedor::where('empresa_id', $this->empresaActivaId())
            ->where('activo', true)->orderBy('nombre')->get();
        $unidadesMedida = UnidadMedida::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('insumos.edit', compact('insumo', 'proveedores', 'unidadesMedida'));
        ;
    }

    public function update(Request $request, Insumo $insumo)
    {
        $this->verificarEmpresa($insumo);

        $validated = $request->validate([
            'proveedor_id' => 'nullable|exists:proveedors,id',
            'unidad_medida_id' => 'nullable|exists:unidad_medidas,id',
            'codigo' => 'nullable|string|max:20|unique:insumos,codigo,' . $insumo->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo_unitario' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'required|numeric|min:0|gt:stock_minimo',
            'activo' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $stockAnterior = $insumo->stock;
            $insumo->update($validated);

            if ($validated['stock'] != $stockAnterior) {
                $diferencia = $validated['stock'] - $stockAnterior;
                InventarioMovimiento::create([
                    'empresa_id' => $this->empresaActivaId(),
                    'insumo_id' => $insumo->id,
                    'user_id' => auth()->id(),
                    'tipo' => $diferencia > 0 ? 'entrada' : 'salida',
                    'motivo' => 'ajuste_inventario',
                    'cantidad' => abs($diferencia),
                    'costo_unitario' => $validated['costo_unitario'],
                    'costo_total' => abs($diferencia) * $validated['costo_unitario'],
                    'observacion' => 'Ajuste manual de inventario',
                ]);
            }

            DB::commit();
            return redirect()->route('insumos.index')->with('success', 'Insumo actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar.');
        }
    }

    public function destroy(Insumo $insumo)
    {
        $this->verificarEmpresa($insumo);

        if ($insumo->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar porque tiene productos asociados.');
        }

        try {
            $nombre = $insumo->nombre;
            $insumo->delete();
            return redirect()->route('insumos.index')
                ->with('success', 'Insumo "' . $nombre . '" eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar insumo: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el insumo.');
        }
    }

    public function export()
    {
        $empresaId = $this->empresaActivaId();
        $empresa = \App\Models\Empresa::find($empresaId);
        $fileName = 'insumos_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new InsumosExport($empresaId), $fileName);
    }
    public function toggleActivo(Insumo $insumo)
    {
        try {
            $this->verificarEmpresa($insumo);

            $nuevoEstado = !$insumo->activo;
            $insumo->update(['activo' => $nuevoEstado]);

            return response()->json([
                'success' => true,
                'activo' => $nuevoEstado,
                'message' => 'Insumo ' . ($nuevoEstado ? 'activado' : 'desactivado') . ' correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de insumo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del insumo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function verificarEmpresa(Insumo $insumo)
    {
        $empresaId = $this->empresaActivaId();
        if (auth()->user()->hasRole('Super Admin') && !$empresaId) {
            return;
        }
        if ($insumo->empresa_id !== $empresaId) {
            abort(403, 'Este insumo no pertenece a la empresa activa.');
        }
    }
}