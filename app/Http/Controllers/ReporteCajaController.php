<?php
namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReporteCajaController extends Controller
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

    /**
     * Obtener IDs de cajas apertura según el rol del usuario
     */
    private function getCajaAperturaIds()
    {
        $user = auth()->user();
        $sucursalId = $this->sucursalActivaId();
        $empresaId = $this->empresaActivaId();

        $query = CajaApertura::where('estado', 'abierta')
            ->whereHas('caja', function ($q) use ($empresaId, $sucursalId) {
                $q->where('empresa_id', $empresaId);
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            });

        // ✅ Cajero: solo sus propias cajas abiertas
        if ($user->hasRole('Cajero')) {
            $query->where('user_id', $user->id);
        }
        // ✅ Vendedor y Cobrador: cajas de su sucursal
        // ✅ Admin: cajas de su empresa
        // ✅ Super Admin: cajas de la empresa/sucursal activa

        return $query->pluck('id')->toArray();
    }

    public function dashboard(Request $request)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

            // ✅ Obtener IDs de cajas apertura según el rol
            $cajaAperturaIds = $this->getCajaAperturaIds();

            $datos = $this->getDatosGraficas($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds);
            $resumen = $this->getResumenGeneral($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds);
            $topMovimientos = $this->getTopMovimientos($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds);
            $movimientosRecientes = $this->getMovimientosRecientes($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds);

            // Preparar datos para JavaScript
            $evolucionLabels = [];
            $evolucionIngresos = [];
            $evolucionEgresos = [];
            foreach ($datos['evolucion_diaria'] as $item) {
                $evolucionLabels[] = $item->fecha;
                $evolucionIngresos[] = floatval($item->ingresos);
                $evolucionEgresos[] = floatval($item->egresos);
            }

            $formaPagoLabels = [];
            $formaPagoValues = [];
            foreach ($datos['forma_pago'] as $item) {
                $formaPagoLabels[] = ucfirst(str_replace('_', ' ', $item->forma_pago));
                $formaPagoValues[] = floatval($item->total);
            }

            return view('reportes.caja-dashboard', compact(
                'datos', 'resumen', 'topMovimientos', 'movimientosRecientes',
                'fechaInicio', 'fechaFin',
                'evolucionLabels', 'evolucionIngresos', 'evolucionEgresos',
                'formaPagoLabels', 'formaPagoValues'
            ));
        } catch (\Exception $e) {
            Log::error('Error en dashboard de caja: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el dashboard.');
        }
    }

    /**
     * Aplicar filtro de cajas apertura a una query
     */
    private function filtrarPorCajas($query, $cajaAperturaIds)
    {
        if (!empty($cajaAperturaIds)) {
            $query->whereIn('caja_apertura_id', $cajaAperturaIds);
        }
        return $query;
    }

    private function getDatosGraficas($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds)
    {
        $baseQuery = function ($q) use ($empresaId, $sucursalId, $cajaAperturaIds) {
            if (!empty($cajaAperturaIds)) {
                $q->whereIn('caja_apertura_id', $cajaAperturaIds);
            } else {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) $c->where('sucursal_id', $sucursalId);
                    });
                });
            }
        };

        $evolucionDiaria = CajaMovimiento::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as ingresos'),
            DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as egresos')
        )
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('fecha')->orderBy('fecha')->get();

        $formaPago = CajaMovimiento::select('forma_pago', DB::raw('SUM(monto) as total'))
            ->where('tipo', 'ingreso')
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('forma_pago')->get();

        $categoriaIngresos = CajaMovimiento::select('categoria', DB::raw('SUM(monto) as total'))
            ->where('tipo', 'ingreso')
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('categoria')->get();

        $categoriaEgresos = CajaMovimiento::select('categoria', DB::raw('SUM(monto) as total'))
            ->where('tipo', 'egreso')
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('categoria')->get();

        $porDiaSemana = CajaMovimiento::select(
            DB::raw('DAYOFWEEK(created_at) as dia_numero'),
            DB::raw('DAYNAME(created_at) as dia'),
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as ingresos'),
            DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as egresos')
        )
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('dia_numero', 'dia')->orderBy('dia_numero')->get();

        return [
            'evolucion_diaria' => $evolucionDiaria,
            'forma_pago' => $formaPago,
            'categoria_ingresos' => $categoriaIngresos,
            'categoria_egresos' => $categoriaEgresos,
            'por_dia_semana' => $porDiaSemana,
        ];
    }

    private function getResumenGeneral($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds)
    {
        $baseQuery = function ($q) use ($cajaAperturaIds) {
            if (!empty($cajaAperturaIds)) {
                $q->whereIn('caja_apertura_id', $cajaAperturaIds);
            }
        };

        $totales = CajaMovimiento::select(
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as total_ingresos'),
            DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as total_egresos'),
            DB::raw('COUNT(CASE WHEN tipo = "ingreso" THEN 1 END) as num_ingresos'),
            DB::raw('COUNT(CASE WHEN tipo = "egreso" THEN 1 END) as num_egresos')
        )
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->first();

        $fechaInicioAnterior = \Carbon\Carbon::parse($fechaInicio)->subMonth();
        $fechaFinAnterior = \Carbon\Carbon::parse($fechaFin)->subMonth();

        $totalesAnterior = CajaMovimiento::select(
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as total_ingresos')
        )
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicioAnterior . ' 00:00:00', $fechaFinAnterior . ' 23:59:59'])
            ->first();

        $ingresos = $totales->total_ingresos ?? 0;
        $ingresosAnterior = $totalesAnterior->total_ingresos ?? 0;
        $variacion = $ingresosAnterior > 0 ? (($ingresos - $ingresosAnterior) / $ingresosAnterior) * 100 : 0;

        return [
            'total_ingresos' => $ingresos,
            'total_egresos' => $totales->total_egresos ?? 0,
            'saldo_neto' => $ingresos - ($totales->total_egresos ?? 0),
            'num_ingresos' => $totales->num_ingresos ?? 0,
            'num_egresos' => $totales->num_egresos ?? 0,
            'variacion' => $variacion,
        ];
    }

    private function getTopMovimientos($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds)
    {
        $baseQuery = function ($q) use ($cajaAperturaIds) {
            if (!empty($cajaAperturaIds)) {
                $q->whereIn('caja_apertura_id', $cajaAperturaIds);
            }
        };

        $topIngresos = CajaMovimiento::with(['cajaApertura.caja', 'usuario'])
            ->where('tipo', 'ingreso')
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->orderBy('monto', 'desc')->limit(5)->get();

        $topEgresos = CajaMovimiento::with(['cajaApertura.caja', 'usuario'])
            ->where('tipo', 'egreso')
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->orderBy('monto', 'desc')->limit(5)->get();

        return ['ingresos' => $topIngresos, 'egresos' => $topEgresos];
    }

    private function getMovimientosRecientes($empresaId, $sucursalId, $fechaInicio, $fechaFin, $cajaAperturaIds)
    {
        $baseQuery = function ($q) use ($cajaAperturaIds) {
            if (!empty($cajaAperturaIds)) {
                $q->whereIn('caja_apertura_id', $cajaAperturaIds);
            }
        };

        return CajaMovimiento::with(['cajaApertura.caja', 'usuario'])
            ->where($baseQuery)
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->orderBy('created_at', 'desc')->limit(10)->get();
    }

    public function exportar(Request $request)
    {
        // Implementar si es necesario
    }
}