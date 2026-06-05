<?php

namespace App\Http\Controllers;

use App\Exports\InventarioMovimientosExport;
use App\Models\Producto;
use App\Models\Insumo;
use App\Models\InventarioMovimiento;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;

class InventarioMovimientoController extends Controller
{
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaActivaId();

        $movimientos = InventarioMovimiento::with(['producto', 'insumo', 'usuario', 'sucursalOrigen', 'sucursalDestino'])
            ->where('empresa_id', $empresaId)
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->producto_id, fn($q) => $q->where('producto_id', $request->producto_id))
            ->when($request->fecha_desde, fn($q) => $q->whereDate('created_at', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn($q) => $q->whereDate('created_at', '<=', $request->fecha_hasta))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $productos = Producto::where('empresa_id', $empresaId)->orderBy('nombre')->get();

        return view('inventario.movimientos', compact('movimientos', 'productos'));
    }

    public function create()
    {
        $empresaId = $this->empresaActivaId();
        $productos = Producto::where('empresa_id', $empresaId)->where('activo', true)->orderBy('nombre')->get();
        $insumos = Insumo::where('empresa_id', $empresaId)->where('activo', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::where('empresa_id', $empresaId)->where('activo', true)->orderBy('nombre')->get();

        return view('inventario.movimiento-create', compact('productos', 'insumos', 'sucursales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:entrada,salida,transferencia,ajuste',
            'motivo' => 'required|string',
            'producto_id' => 'nullable|integer',
            'insumo_id' => 'nullable|integer',
            'sucursal_origen_id' => 'nullable|integer',
            'sucursal_destino_id' => 'nullable|integer',
            'cantidad' => 'required|numeric|min:0.01',
            'costo_unitario' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
        ]);

        if (!$validated['producto_id'] && !$validated['insumo_id']) {
            return back()->with('error', 'Debe seleccionar un producto o insumo.');
        }

        DB::beginTransaction();
        try {
            $costoTotal = $validated['cantidad'] * $validated['costo_unitario'];

            $movimiento = InventarioMovimiento::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_origen_id' => $validated['sucursal_origen_id'] ?? null,
                'sucursal_destino_id' => $validated['sucursal_destino_id'] ?? null,
                'producto_id' => $validated['producto_id'] ?? null,
                'insumo_id' => $validated['insumo_id'] ?? null,
                'user_id' => auth()->id(),
                'tipo' => $validated['tipo'],
                'motivo' => $validated['motivo'],
                'cantidad' => $validated['cantidad'],
                'costo_unitario' => $validated['costo_unitario'],
                'costo_total' => $costoTotal,
                'observacion' => $validated['observacion'] ?? null,
            ]);

            // Actualizar stock
            if ($validated['producto_id']) {
                $producto = Producto::find($validated['producto_id']);
                if ($producto->control_inventario) {
                    if (in_array($validated['tipo'], ['entrada', 'ajuste'])) {
                        $producto->increment('stock', $validated['cantidad']);
                    } elseif (in_array($validated['tipo'], ['salida'])) {
                        if ($producto->stock < $validated['cantidad']) {
                            throw new \Exception('Stock insuficiente. Stock actual: ' . $producto->stock);
                        }
                        $producto->decrement('stock', $validated['cantidad']);
                    } elseif ($validated['tipo'] == 'transferencia') {
                        // En transferencia no se modifica el stock total, solo se registra
                    }
                }
            }

            if ($validated['insumo_id']) {
                $insumo = Insumo::find($validated['insumo_id']);
                if (in_array($validated['tipo'], ['entrada', 'ajuste'])) {
                    $insumo->increment('stock', $validated['cantidad']);
                } elseif (in_array($validated['tipo'], ['salida'])) {
                    if ($insumo->stock < $validated['cantidad']) {
                        throw new \Exception('Stock insuficiente de insumo.');
                    }
                    $insumo->decrement('stock', $validated['cantidad']);
                }
            }

            DB::commit();
            return redirect()->route('inventario.movimientos')
                ->with('success', 'Movimiento registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en movimiento: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
    public function export(Request $request)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = \App\Models\Empresa::find($empresaId);
            $fileName = 'movimientos_inventario_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';
            return Excel::download(new InventarioMovimientosExport($empresaId), $fileName);
        } catch (\Exception $e) {
            Log::error('Error al exportar movimientos: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
}