<?php
// app/Http/Controllers/CotizacionController.php

namespace App\Http\Controllers;

use App\Models\CajaApertura;
use App\Models\Carrito;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CotizacionController extends Controller
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
    public function index(Request $request)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();
            $user = auth()->user();

            // ✅ Filtrar por caja si se selecciona una
            $cajaSeleccionadaId = $request->caja_id ?? session('caja_filtro_id');

            $cotizaciones = Cotizacion::where('empresa_id', $empresaId)
                ->when($cajaSeleccionadaId, function ($q) use ($cajaSeleccionadaId) {
                    // Si tienes relación con caja_apertura_id en cotizaciones
                    // return $q->where('caja_apertura_id', $cajaSeleccionadaId);
                    // O si no, mostrar todas (el filtro es solo visual)
                })
                ->with(['usuario', 'cliente', 'detalles'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Obtener cajas activas
            $cajaAbierta = null;
            $cajasActivas = collect();

            if ($user->hasRole('Vendedor') || $user->hasRole('Cobrador')) {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')->with(['caja', 'usuario'])->get();
                if ($cajasActivas->count() === 1)
                    $cajaAbierta = $cajasActivas->first();
            } elseif ($user->hasRole('Cajero')) {
                $cajaAbierta = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('user_id', auth()->id())->where('estado', 'abierta')->with(['caja', 'usuario'])->first();
            } else {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')->with(['caja', 'usuario'])->get();
                if ($cajasActivas->count() === 1)
                    $cajaAbierta = $cajasActivas->first();
            }

            return view('cotizaciones.index', compact('cotizaciones', 'cajaAbierta', 'cajasActivas', 'cajaSeleccionadaId'));

        } catch (\Exception $e) {
            Log::error('Error en cotizaciones index: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar cotizaciones.');
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $items = $request->items;
            $subtotal = 0;

            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['id']);
                $totalItem = $producto->precio_venta * $item['cantidad'];
                $subtotal += $totalItem;
            }

            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;

            // Convertir a entero (int)
            $diasValidez = intval($request->dias_validez ?? 7);
            // Obtener sucursal activa usando la función
            $sucursalId = $this->sucursalActivaId();

            // Si el Super Admin no tiene sucursal en sesión, obtener la primera disponible
            if (!$sucursalId && auth()->user()->hasRole('Super Admin')) {
                $sucursal = Sucursal::where('empresa_id', $this->empresaActivaId())
                    ->where('activo', true)
                    ->first();
                if ($sucursal) {
                    $sucursalId = $sucursal->id;
                    session(['sucursal_activa_id' => $sucursalId]);
                }
            }
            $cotizacion = Cotizacion::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $sucursalId,
                'user_id' => auth()->id(),
                'cliente_id' => $request->cliente_id ?: null,
                'folio' => Cotizacion::generarFolio(),
                'estado' => 'activa',
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'observaciones' => $request->observaciones,
                'fecha_validez' => now()->addDays($diasValidez), // Ahora es entero
                'fecha_cotizacion' => now()
            ]);

            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['id']);
                $subtotalItem = $producto->precio_venta * $item['cantidad'];

                $cotizacion->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $subtotalItem
                ]);
            }
            // ✅ LIMPIAR CARRITO DESPUÉS DE GENERAR COTIZACIÓN
            $carrito = Carrito::obtenerCarrito();
            $carrito->limpiar();
            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $cotizacion->id,
                'folio' => $cotizacion->folio,
                'message' => 'Cotización generada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function pdf($id)
    {
        $cotizacion = Cotizacion::with(['detalles.producto', 'usuario', 'cliente'])->findOrFail($id);
        $empresa = Empresa::find($this->empresaActivaId());

        // Calcular días de validez correctamente (entero)
        $diasValidez = 7; // valor por defecto
        if ($cotizacion->fecha_validez) {
            $fechaValidez = \Carbon\Carbon::parse($cotizacion->fecha_validez);
            $diasValidez = $fechaValidez->diffInDays($cotizacion->fecha_cotizacion);
        }

        // Redondear a entero
        $diasValidez = round($diasValidez);

        $pdf = Pdf::loadView('cotizaciones.pdf', compact('cotizacion', 'empresa', 'diasValidez'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Cotizacion_{$cotizacion->folio}.pdf");
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with(['detalles.producto', 'usuario', 'cliente'])->findOrFail($id);
        return view('cotizaciones.show', compact('cotizacion'));
    }

    public function destroy($id)
    {
        try {
            $cotizacion = Cotizacion::findOrFail($id);
            $cotizacion->update(['estado' => 'cancelada']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización cancelada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function cargarCarrito($id)
    {
        try {
            $cotizacion = Cotizacion::with('detalles.producto')->findOrFail($id);

            $items = [];
            foreach ($cotizacion->detalles as $detalle) {
                $producto = $detalle->producto;

                if ($producto->stock < $detalle->cantidad) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}"
                    ], 400);
                }

                $items[] = [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $detalle->precio_unitario,
                    'cantidad' => $detalle->cantidad
                ];
            }

            session(['carrito_cotizacion' => $items]);

            return response()->json([
                'success' => true,
                'items' => $items,
                'total_items' => count($items)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}