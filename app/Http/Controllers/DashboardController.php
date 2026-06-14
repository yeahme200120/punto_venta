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

    /**
     * Obtener la caja actual según el rol del usuario
     */
    private function getCajaActual()
    {
        $user = auth()->user();
        $sucursalId = $this->sucursalActivaId();
        $empresaId = $this->empresaActivaId();
        $userId = auth()->id();

        // Super Admin y Administrador: pueden tener una caja seleccionada en sesión
        if ($user->hasRole('Super Admin') || $user->hasRole('Administrador')) {
            $aperturaIdSession = session('caja_operacion_id');
            if ($aperturaIdSession) {
                $apertura = \App\Models\CajaApertura::with(['caja', 'usuario'])
                    ->where('id', $aperturaIdSession)
                    ->where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')
                    ->first();
                if ($apertura) {
                    return $apertura;
                }
            }

            // Si no hay seleccionada, obtener la primera caja abierta
            $apertura = \App\Models\CajaApertura::with(['caja', 'usuario'])
                ->where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', 'abierta')
                ->first();

            return $apertura;
        }

        // Usuarios normales: solo su propia caja
        return \App\Models\CajaApertura::with(['caja', 'usuario'])
            ->where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('user_id', $userId)
            ->where('estado', 'abierta')
            ->whereDate('fecha', today())
            ->first();
    }

    /**
     * Obtener todas las cajas abiertas (para selectores de Admin/Super Admin)
     */
    private function getTodasAperturas()
    {
        $user = auth()->user();
        $sucursalId = $this->sucursalActivaId();
        $empresaId = $this->empresaActivaId();

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador')) {
            return collect();
        }

        return \App\Models\CajaApertura::with(['caja', 'usuario'])
            ->where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function index()
    {
        $user = auth()->user();
        $empresaId = $this->empresaActivaId();
        $sucursalId = $this->sucursalActivaId();

        // 🔥 Obtener caja actual
        $cajaActual = $this->getCajaActual();
        $todasAperturas = $this->getTodasAperturas();

        // 🔥 Obtener el ID de la apertura actual para filtrar movimientos
        $aperturaId = $cajaActual ? $cajaActual->id : null;

        // Fechas
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $inicioAnio = Carbon::now()->startOfYear();

        // ========== KPIs PRINCIPALES FILTRADOS POR CAJA ==========

        // 🔥 Ventas (desde movimientos de caja, no directamente de ventas)
        $ventasHoy = $this->getMovimientosPorCaja($aperturaId, 'ingreso', 'venta', $hoy);
        $ventasMes = $this->getMovimientosPorCaja($aperturaId, 'ingreso', 'venta', $inicioMes, now());
        $ventasAnio = $this->getMovimientosPorCaja($aperturaId, 'ingreso', 'venta', $inicioAnio, now());

        // 🔥 Cobranza (pagos recibidos)
        $cobranzaHoy = $this->getMovimientosPorCaja($aperturaId, 'ingreso', 'abono_credito', $hoy);
        $cobranzaMes = $this->getMovimientosPorCaja($aperturaId, 'ingreso', 'abono_credito', $inicioMes, now());
        $cobranzaAnio = $this->getMovimientosPorCaja($aperturaId, 'ingreso', 'abono_credito', $inicioAnio, now());

        // 🔥 Créditos (esto es independiente de la caja, son saldos pendientes)
        $creditosActivos = Credito::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'activo')
            ->count();

        $saldoPendiente = Credito::where('empresa_id', $empresaId)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->where('estado', 'activo')
            ->sum('saldo_pendiente') ?? 0;

        // Productos con stock bajo (independiente de caja)
        $productosStockBajo = Producto::where('empresa_id', $empresaId)
            ->where('control_inventario', true)
            ->whereRaw('stock <= stock_minimo')
            ->count();

        $totalUsuarios = User::where('empresa_id', $empresaId)->count();

        // Empresa y licencia
        $empresa = $user->empresa;
        $licencia = $empresa ? $empresa->licencia : null;
        $diasRestantes = ($empresa && $empresa->fecha_fin) ? now()->diffInDays($empresa->fecha_fin, false) : 0;

        // ========== GRÁFICOS FILTRADOS POR CAJA ==========

        // Ventas por mes (últimos 12 meses)
        $ventasPorMes = $this->ventasPorMesPorCaja($aperturaId, $sucursalId);
        $cobranzaPorMes = $this->cobranzaPorMesPorCaja($aperturaId, $sucursalId);
        $ingresosFormaPago = $this->ingresosPorFormaPagoPorCaja($aperturaId, $inicioMes, now());
        $topClientes = $this->topClientesPorCaja($empresaId, $sucursalId, $aperturaId, $inicioAnio);

        return view('dashboard.index', compact(
            'totalUsuarios',
            'empresa',
            'licencia',
            'diasRestantes',
            'ventasHoy',
            'ventasMes',
            'ventasAnio',
            'cobranzaHoy',
            'cobranzaMes',
            'cobranzaAnio',
            'creditosActivos',
            'saldoPendiente',
            'productosStockBajo',
            'ventasPorMes',
            'cobranzaPorMes',
            'ingresosFormaPago',
            'topClientes',
            'cajaActual',
            'todasAperturas'
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
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
    private function getMovimientosPorCaja($aperturaId, $tipo, $categoria, $fechaInicio, $fechaFin = null)
    {
        if (!$aperturaId) {
            return 0;
        }

        $query = \App\Models\CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', $tipo)
            ->where('categoria', $categoria)
            ->where('requiere_autorizacion', false); // Solo movimientos autorizados

        if ($fechaFin) {
            $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        } else {
            $query->whereDate('created_at', $fechaInicio);
        }

        return $query->sum('monto') ?? 0;
    }
    /**
     * Ventas por mes filtrado por caja
     */
    private function ventasPorMesPorCaja($aperturaId, $sucursalId)
    {
        if (!$aperturaId) {
            return $this->mesesVacios();
        }

        $resultado = \App\Models\CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'ingreso')
            ->where('categoria', 'venta')
            ->where('requiere_autorizacion', false)
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'), DB::raw('SUM(monto) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();

        return $this->completarMeses($resultado);
    }

    /**
     * Cobranza por mes filtrado por caja
     */
    private function cobranzaPorMesPorCaja($aperturaId, $sucursalId)
    {
        if (!$aperturaId) {
            return $this->mesesVacios();
        }

        $resultado = \App\Models\CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'ingreso')
            ->where('categoria', 'abono_credito')
            ->where('requiere_autorizacion', false)
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'), DB::raw('SUM(monto) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();

        return $this->completarMeses($resultado);
    }

    /**
     * Ingresos por forma de pago filtrado por caja
     */
    private function ingresosPorFormaPagoPorCaja($aperturaId, $fechaInicio, $fechaFin)
    {
        if (!$aperturaId) {
            return [];
        }

        return \App\Models\CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'ingreso')
            ->where('requiere_autorizacion', false)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->select('forma_pago', DB::raw('SUM(monto) as total'))
            ->groupBy('forma_pago')
            ->get()
            ->pluck('total', 'forma_pago')
            ->toArray();
    }

    /**
     * Top clientes por caja
     */
    private function topClientesPorCaja($empresaId, $sucursalId, $aperturaId, $fechaInicio)
    {
        if (!$aperturaId) {
            return collect();
        }

        // Obtener IDs de ventas desde los movimientos de caja
        $ventaIds = \App\Models\CajaMovimiento::where('caja_apertura_id', $aperturaId)
            ->where('tipo', 'ingreso')
            ->where('categoria', 'venta')
            ->where('requiere_autorizacion', false)
            ->where('created_at', '>=', $fechaInicio)
            ->whereNotNull('referencia_id')
            ->where('referencia_type', 'App\Models\Venta')
            ->pluck('referencia_id')
            ->toArray();

        if (empty($ventaIds)) {
            return collect();
        }

        return Venta::whereIn('ventas.id', $ventaIds)
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

    /**
     * Generar array de meses vacíos para los últimos 12 meses
     */
    private function mesesVacios()
    {
        $meses = [];
        $fechaInicio = Carbon::now()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mes = $fechaInicio->copy()->addMonths($i)->format('Y-m');
            $meses[$mes] = 0;
        }
        return $meses;
    }

    /**
     * Completar meses faltantes con ceros
     */
    private function completarMeses($data)
    {
        $mesesCompletos = [];
        $fechaInicio = Carbon::now()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mes = $fechaInicio->copy()->addMonths($i)->format('Y-m');
            $mesesCompletos[$mes] = $data[$mes] ?? 0;
        }
        return $mesesCompletos;
    }
}