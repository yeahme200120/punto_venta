<?php
// app/Http/Controllers/ReporteController.php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Producto;
use App\Models\Cobranza;
use App\Models\Credito;
use App\Models\Cliente;
use App\Models\FormaPago;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    use ActivaTrait;

    // ==================== REPORTE DE VENTAS ====================
    public function ventas(Request $request)
    {
        $empresaId = $this->empresaActivaId();
        $sucursalId = $this->sucursalActivaId();

        // Obtener valores para filtros
        $clientes = Cliente::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('nombre')
            ->get();

        $formasPago = FormaPago::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        // Fechas por defecto: último mes
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->subMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));

        // Construir consulta
        $ventas = Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->when($request->cliente_id, fn($q) => $q->where('cliente_id', $request->cliente_id))
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->forma_pago_id, function($q) use ($request) {
                $q->whereHas('pagoDetalles', function($q2) use ($request) {
                    $q2->where('forma_pago_id', $request->forma_pago_id);
                });
            })
            ->whereBetween('fecha_venta', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['cliente', 'usuario', 'detalles.producto', 'pagoDetalles.formaPago'])
            ->orderBy('fecha_venta', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Resumen para gráfico (ventas por día)
        $ventasPorDia = Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_venta', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->select(DB::raw('DATE(fecha_venta) as fecha'), DB::raw('SUM(total) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Totales
        $totalVentas = $ventas->total();
        $totalMonto = $ventas->sum('total');
        $totalContado = $ventas->where('tipo', 'contado')->sum('total');
        $totalCredito = $ventas->where('tipo', 'credito')->sum('total');

        return view('reportes.ventas', compact(
            'ventas', 'clientes', 'formasPago', 'fechaInicio', 'fechaFin',
            'ventasPorDia', 'totalVentas', 'totalMonto', 'totalContado', 'totalCredito'
        ));
    }

    // ==================== REPORTE DE INVENTARIO ====================
    public function inventario(Request $request)
    {
        $empresaId = $this->empresaActivaId();

        $productos = Producto::where('empresa_id', $empresaId)
            ->with('categoria')
            ->when($request->categoria_id, fn($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->stock_bajo == 1, fn($q) => $q->stockBajo())
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->orderBy('nombre')
            ->paginate(20)
            ->appends($request->all());

        // Top productos más vendidos (últimos 12 meses)
        $topProductos = VentaDetalle::whereHas('venta', function($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                  ->where('fecha_venta', '>=', Carbon::now()->subMonths(12));
            })
            ->select('producto_id', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(subtotal) as total_venta'))
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total_venta')
            ->limit(10)
            ->get();

        // Movimientos de inventario (si se solicita)
        $movimientos = null;
        if ($request->movimientos) {
            $movimientos = \App\Models\InventarioMovimiento::where('empresa_id', $empresaId)
                ->when($request->producto_id, fn($q) => $q->where('producto_id', $request->producto_id))
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        // Categorías para filtro
        $categorias = \App\Models\Categoria::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('reportes.inventario', compact('productos', 'topProductos', 'movimientos', 'categorias'));
    }

    // ==================== REPORTE DE COBRANZA ====================
    public function cobranza(Request $request)
    {
        $empresaId = $this->empresaActivaId();
        $sucursalId = $this->sucursalActivaId();

        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->subMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));

        // Cobros realizados en el período
        $cobros = Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->when($request->cliente_id, function($q) use ($request) {
                $q->whereHas('credito', fn($q2) => $q2->where('cliente_id', $request->cliente_id));
            })
            ->whereBetween('fecha_cobro', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['credito.cliente', 'usuario', 'pagare'])
            ->orderBy('fecha_cobro', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Créditos activos
        $creditosActivos = Credito::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'activo')
            ->with('cliente')
            ->orderBy('fecha_fin')
            ->get();

        // Resumen de cobranza por día (gráfico)
        $cobranzaPorDia = Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_cobro', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->select(DB::raw('DATE(fecha_cobro) as fecha'), DB::raw('SUM(monto) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Totales
        $totalCobrado = $cobros->sum('monto');
        $totalPendiente = $creditosActivos->sum('saldo_pendiente');
        $totalCreditos = $creditosActivos->count();

        // Clientes para filtro
        $clientes = Cliente::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('nombre')
            ->get();

        return view('reportes.cobranza', compact(
            'cobros', 'creditosActivos', 'cobranzaPorDia',
            'totalCobrado', 'totalPendiente', 'totalCreditos',
            'fechaInicio', 'fechaFin', 'clientes'
        ));
    }

    // ==================== EXPORTACIONES (CSV) ====================
    public function exportarVentas(Request $request)
    {
        $empresaId = $this->empresaActivaId();
        $sucursalId = $this->sucursalActivaId();

        $ventas = Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->when($request->cliente_id, fn($q) => $q->where('cliente_id', $request->cliente_id))
            ->whereBetween('fecha_venta', [$request->fecha_inicio . ' 00:00:00', $request->fecha_fin . ' 23:59:59'])
            ->with(['cliente', 'usuario'])
            ->orderBy('fecha_venta')
            ->get();

        $filename = 'ventas_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['Folio', 'Fecha', 'Cliente', 'Tipo', 'Subtotal', 'IVA', 'Total', 'Usuario']);

        foreach ($ventas as $v) {
            fputcsv($handle, [
                $v->folio,
                $v->fecha_venta->format('d/m/Y H:i'),
                $v->cliente->nombre ?? 'Mostrador',
                $v->tipo,
                $v->subtotal,
                $v->iva,
                $v->total,
                $v->usuario->name
            ]);
        }
        fclose($handle);

        return response()->stream(
            fn() => null,
            200,
            ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""]
        );
    }

    public function exportarCobranza(Request $request)
    {
        $empresaId = $this->empresaActivaId();
        $sucursalId = $this->sucursalActivaId();

        $cobros = Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_cobro', [$request->fecha_inicio . ' 00:00:00', $request->fecha_fin . ' 23:59:59'])
            ->with(['credito.cliente', 'usuario'])
            ->get();

        $filename = 'cobranza_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['Fecha', 'Cliente', 'Crédito', 'Monto', 'Tipo', 'Usuario', 'Observaciones']);

        foreach ($cobros as $c) {
            fputcsv($handle, [
                $c->fecha_cobro->format('d/m/Y H:i'),
                $c->credito->cliente->nombre ?? 'N/A',
                $c->credito->venta->folio ?? 'N/A',
                $c->monto,
                $c->tipo,
                $c->usuario->name,
                $c->observaciones
            ]);
        }
        fclose($handle);

        return response()->stream(
            fn() => null,
            200,
            ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""]
        );
    }
}