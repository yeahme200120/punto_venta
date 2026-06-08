<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venta;
use App\Models\Cobranza;
use App\Models\Credito;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\FormaPago;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ActivaTrait;

    public function index()
    {
        $user = auth()->user();
        $empresaId = $this->empresaActivaId();
        $sucursalId = $this->sucursalActivaId();

        // Fechas
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $inicioAnio = Carbon::now()->startOfYear();

        // KPIs básicos (si no hay empresa, valores por defecto)
        $totalUsuarios = $empresaId ? User::where('empresa_id', $empresaId)->count() : 0;
        $empresa = $user->empresa ?? null;
        $licencia = $empresa ? $empresa->licencia : null;
        $diasRestantes = ($empresa && $empresa->fecha_fin) ? now()->diffInDays($empresa->fecha_fin, false) : 0;

        // --- Ventas ---
        $ventasHoy = $this->obtenerSumaVenta($empresaId, $sucursalId, $hoy, $hoy);
        $ventasMes = $this->obtenerSumaVenta($empresaId, $sucursalId, $inicioMes, now());
        $ventasAnio = $this->obtenerSumaVenta($empresaId, $sucursalId, $inicioAnio, now());

        // --- Cobranza ---
        $cobranzaHoy = $this->obtenerSumaCobranza($empresaId, $sucursalId, $hoy, $hoy);
        $cobranzaMes = $this->obtenerSumaCobranza($empresaId, $sucursalId, $inicioMes, now());
        $cobranzaAnio = $this->obtenerSumaCobranza($empresaId, $sucursalId, $inicioAnio, now());

        // --- Créditos ---
        $creditosActivos = $empresaId ? Credito::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'activo')
            ->count() : 0;

        $saldoPendiente = $empresaId ? Credito::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'activo')
            ->sum('saldo_pendiente') : 0;

        // --- Productos con stock bajo ---
        $productosStockBajo = $empresaId ? Producto::where('empresa_id', $empresaId)
            ->where('control_inventario', true)
            ->whereRaw('stock <= stock_minimo')
            ->count() : 0;

        // --- Datos para gráficos (si no hay empresa, arrays vacíos) ---
        $ventasPorMes = $empresaId ? $this->ventasPorMes($empresaId, $sucursalId) : [];
        $cobranzaPorMes = $empresaId ? $this->cobranzaPorMes($empresaId, $sucursalId) : [];
        $ingresosFormaPago = $empresaId ? $this->ingresosPorFormaPago($empresaId, $sucursalId, $inicioMes, now()) : [];
        $topClientes = $empresaId ? $this->topClientes($empresaId, $sucursalId, $inicioAnio) : collect();

        return view('dashboard.index', compact(
            'totalUsuarios', 'empresa', 'licencia', 'diasRestantes',
            'ventasHoy', 'ventasMes', 'ventasAnio',
            'cobranzaHoy', 'cobranzaMes', 'cobranzaAnio',
            'creditosActivos', 'saldoPendiente', 'productosStockBajo',
            'ventasPorMes', 'cobranzaPorMes', 'ingresosFormaPago', 'topClientes'
        ));
    }

    private function obtenerSumaVenta($empresaId, $sucursalId, $fechaInicio, $fechaFin)
    {
        if (!$empresaId) return 0;
        return Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->sum('total') ?? 0;
    }

    private function obtenerSumaCobranza($empresaId, $sucursalId, $fechaInicio, $fechaFin)
    {
        if (!$empresaId) return 0;
        return Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_cobro', [$fechaInicio, $fechaFin])
            ->sum('monto') ?? 0;
    }

    private function ventasPorMes($empresaId, $sucursalId)
    {
        $resultado = Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->select(DB::raw('DATE_FORMAT(fecha_venta, "%Y-%m") as mes'), DB::raw('SUM(total) as total'))
            ->where('fecha_venta', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();

        // Completar meses faltantes
        $mesesCompletos = [];
        $fechaInicio = Carbon::now()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mes = $fechaInicio->copy()->addMonths($i)->format('Y-m');
            $mesesCompletos[$mes] = $resultado[$mes] ?? 0;
        }
        return $mesesCompletos;
    }

    private function cobranzaPorMes($empresaId, $sucursalId)
    {
        $resultado = Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->select(DB::raw('DATE_FORMAT(fecha_cobro, "%Y-%m") as mes'), DB::raw('SUM(monto) as total'))
            ->where('fecha_cobro', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();

        $mesesCompletos = [];
        $fechaInicio = Carbon::now()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mes = $fechaInicio->copy()->addMonths($i)->format('Y-m');
            $mesesCompletos[$mes] = $resultado[$mes] ?? 0;
        }
        return $mesesCompletos;
    }

    private function ingresosPorFormaPago($empresaId, $sucursalId, $fechaInicio, $fechaFin)
    {
        return Cobranza::where('cobranzas.empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('cobranzas.sucursal_id', $sucursalId))
            ->join('caja_movimientos', 'cobranzas.caja_movimiento_id', '=', 'caja_movimientos.id')
            ->whereBetween('cobranzas.fecha_cobro', [$fechaInicio, $fechaFin])
            ->select('caja_movimientos.forma_pago', DB::raw('SUM(cobranzas.monto) as total'))
            ->groupBy('caja_movimientos.forma_pago')
            ->get()
            ->pluck('total', 'forma_pago')
            ->toArray();
    }

    private function topClientes($empresaId, $sucursalId, $fechaInicio)
    {
        return Venta::where('ventas.empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('ventas.sucursal_id', $sucursalId))
            ->where('ventas.fecha_venta', '>=', $fechaInicio)
            ->whereNotNull('cliente_id')
            ->select('cliente_id', DB::raw('SUM(total) as total_gastado'))
            ->with('cliente:id,nombre')
            ->groupBy('cliente_id')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'nombre' => $item->cliente?->nombre ?? 'Sin nombre',
                'total' => $item->total_gastado
            ]);
    }

    public function exportar(Request $request)
    {
        $empresaId = $this->empresaActivaId();
        $sucursalId = $this->sucursalActivaId();
        $tipo = $request->get('tipo', 'ventas_mes');

        $fileName = 'dashboard_' . $tipo . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($tipo, $empresaId, $sucursalId) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            switch ($tipo) {
                case 'ventas_mes':
                    fputcsv($handle, ['Mes', 'Ventas totales']);
                    $data = $this->ventasPorMes($empresaId, $sucursalId);
                    foreach ($data as $mes => $total) {
                        fputcsv($handle, [$mes, $total]);
                    }
                    break;
                case 'cobranza_mes':
                    fputcsv($handle, ['Mes', 'Cobranza total']);
                    $data = $this->cobranzaPorMes($empresaId, $sucursalId);
                    foreach ($data as $mes => $total) {
                        fputcsv($handle, [$mes, $total]);
                    }
                    break;
                case 'top_clientes':
                    fputcsv($handle, ['Cliente', 'Total gastado (último año)']);
                    $data = $this->topClientes($empresaId, $sucursalId, Carbon::now()->startOfYear());
                    foreach ($data as $row) {
                        fputcsv($handle, [$row['nombre'], $row['total']]);
                    }
                    break;
                default:
                    fputcsv($handle, ['No se encontraron datos para el tipo solicitado']);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}