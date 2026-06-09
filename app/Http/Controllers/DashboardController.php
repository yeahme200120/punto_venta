<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venta;
use App\Models\Cobranza;
use App\Models\Credito;
use App\Models\Producto;
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

        // ========== KPIs PRINCIPALES ==========
        
        // Ventas
        $ventasHoy = Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereDate('fecha_venta', $hoy)
            ->sum('total') ?? 0;

        $ventasMes = Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_venta', [$inicioMes, now()])
            ->sum('total') ?? 0;

        $ventasAnio = Venta::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_venta', [$inicioAnio, now()])
            ->sum('total') ?? 0;

        // Cobranza (pagos recibidos)
        $cobranzaHoy = Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereDate('fecha_cobro', $hoy)
            ->sum('monto') ?? 0;

        $cobranzaMes = Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_cobro', [$inicioMes, now()])
            ->sum('monto') ?? 0;

        $cobranzaAnio = Cobranza::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->whereBetween('fecha_cobro', [$inicioAnio, now()])
            ->sum('monto') ?? 0;

        // Créditos
        $creditosActivos = Credito::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'activo')
            ->count();

        $saldoPendiente = Credito::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'activo')
            ->sum('saldo_pendiente') ?? 0;

        // Productos con stock bajo
        $productosStockBajo = Producto::where('empresa_id', $empresaId)
            ->where('control_inventario', true)
            ->whereRaw('stock <= stock_minimo')
            ->count();

        $totalUsuarios = User::where('empresa_id', $empresaId)->count();

        // Empresa y licencia
        $empresa = $user->empresa;
        $licencia = $empresa ? $empresa->licencia : null;
        $diasRestantes = ($empresa && $empresa->fecha_fin) ? now()->diffInDays($empresa->fecha_fin, false) : 0;

        // ========== GRÁFICOS ==========

        // Ventas por mes (últimos 12 meses)
        $ventasPorMes = $this->ventasPorMes($empresaId, $sucursalId);
        $cobranzaPorMes = $this->cobranzaPorMes($empresaId, $sucursalId);
        $ingresosFormaPago = $this->ingresosPorFormaPago($empresaId, $sucursalId, $inicioMes, now());
        $topClientes = $this->topClientes($empresaId, $sucursalId, $inicioAnio);

        return view('dashboard.index', compact(
            'totalUsuarios', 'empresa', 'licencia', 'diasRestantes',
            'ventasHoy', 'ventasMes', 'ventasAnio',
            'cobranzaHoy', 'cobranzaMes', 'cobranzaAnio',
            'creditosActivos', 'saldoPendiente', 'productosStockBajo',
            'ventasPorMes', 'cobranzaPorMes', 'ingresosFormaPago', 'topClientes'
        ));
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
        // Unir cobranzas con caja_movimientos para obtener la forma de pago
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