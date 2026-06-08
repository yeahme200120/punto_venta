<?php
// app/Http/Controllers/ReporteCajaController.php
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


    public function dashboard(Request $request)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

            $datos = $this->getDatosGraficas($empresaId, $sucursalId, $fechaInicio, $fechaFin);
            $resumen = $this->getResumenGeneral($empresaId, $sucursalId, $fechaInicio, $fechaFin);
            $topMovimientos = $this->getTopMovimientos($empresaId, $sucursalId, $fechaInicio, $fechaFin);

            // Preparar datos para JavaScript (sin closures)
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

            $movimientosRecientes = $this->getMovimientosRecientes($empresaId, $sucursalId, $fechaInicio, $fechaFin);

            return view('reportes.caja-dashboard', compact(
                'datos',
                'resumen',
                'topMovimientos',
                'movimientosRecientes',
                'fechaInicio',
                'fechaFin',
                'evolucionLabels',
                'evolucionIngresos',
                'evolucionEgresos',
                'formaPagoLabels',
                'formaPagoValues'
            ));
        } catch (\Exception $e) {
            Log::error('Error en dashboard de caja: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el dashboard.');
        }
    }

    /**
     * Obtener datos para gráficas
     */
    private function getDatosGraficas($empresaId, $sucursalId, $fechaInicio, $fechaFin)
    {
        // 1. Evolución diaria (ingresos vs egresos)
        $evolucionDiaria = CajaMovimiento::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as ingresos'),
            DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as egresos')
        )
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // 2. Distribución por forma de pago
        $formaPago = CajaMovimiento::select(
            'forma_pago',
            DB::raw('SUM(monto) as total')
        )
            ->where('tipo', 'ingreso')
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('forma_pago')
            ->get();

        // 3. Distribución por categoría de ingresos
        $categoriaIngresos = CajaMovimiento::select(
            'categoria',
            DB::raw('SUM(monto) as total')
        )
            ->where('tipo', 'ingreso')
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('categoria')
            ->get();

        // 4. Distribución por categoría de egresos
        $categoriaEgresos = CajaMovimiento::select(
            'categoria',
            DB::raw('SUM(monto) as total')
        )
            ->where('tipo', 'egreso')
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('categoria')
            ->get();

        // 5. Movimientos por día de la semana
        $porDiaSemana = CajaMovimiento::select(
            DB::raw('DAYOFWEEK(created_at) as dia_numero'),
            DB::raw('DAYNAME(created_at) as dia'),
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as ingresos'),
            DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as egresos')
        )
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy('dia_numero', 'dia')
            ->orderBy('dia_numero')
            ->get();

        return [
            'evolucion_diaria' => $evolucionDiaria,
            'forma_pago' => $formaPago,
            'categoria_ingresos' => $categoriaIngresos,
            'categoria_egresos' => $categoriaEgresos,
            'por_dia_semana' => $porDiaSemana,
        ];
    }

    /**
     * Obtener resumen general
     */
    private function getResumenGeneral($empresaId, $sucursalId, $fechaInicio, $fechaFin)
    {
        $totales = CajaMovimiento::select(
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as total_ingresos'),
            DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as total_egresos'),
            DB::raw('COUNT(CASE WHEN tipo = "ingreso" THEN 1 END) as num_ingresos'),
            DB::raw('COUNT(CASE WHEN tipo = "egreso" THEN 1 END) as num_egresos')
        )
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->first();

        // Comparativa con mes anterior
        $fechaInicioAnterior = \Carbon\Carbon::parse($fechaInicio)->subMonth();
        $fechaFinAnterior = \Carbon\Carbon::parse($fechaFin)->subMonth();

        $totalesAnterior = CajaMovimiento::select(
            DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as total_ingresos')
        )
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicioAnterior . ' 00:00:00', $fechaFinAnterior . ' 23:59:59'])
            ->first();

        $ingresos = $totales->total_ingresos ?? 0;
        $ingresosAnterior = $totalesAnterior->total_ingresos ?? 0;
        $variacion = $ingresosAnterior > 0 ? (($ingresos - $ingresosAnterior) / $ingresosAnterior) * 100 : 0;

        return [
            'total_ingresos' => $ingresos,
            'total_egresos' => $totales->total_egresos ?? 0,
            'saldo_neto' => ($totales->total_ingresos ?? 0) - ($totales->total_egresos ?? 0),
            'num_ingresos' => $totales->num_ingresos ?? 0,
            'num_egresos' => $totales->num_egresos ?? 0,
            'promedio_ingreso' => ($totales->num_ingresos ?? 0) > 0 ? ($totales->total_ingresos ?? 0) / ($totales->num_ingresos ?? 0) : 0,
            'promedio_egreso' => ($totales->num_egresos ?? 0) > 0 ? ($totales->total_egresos ?? 0) / ($totales->num_egresos ?? 0) : 0,
            'variacion' => $variacion,
            'tendencia' => $variacion > 0 ? 'up' : ($variacion < 0 ? 'down' : 'stable')
        ];
    }

    /**
     * Obtener top movimientos
     */
    private function getTopMovimientos($empresaId, $sucursalId, $fechaInicio, $fechaFin)
    {
        $topIngresos = CajaMovimiento::with(['cajaApertura.caja', 'usuario'])
            ->where('tipo', 'ingreso')
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->orderBy('monto', 'desc')
            ->limit(5)
            ->get();

        $topEgresos = CajaMovimiento::with(['cajaApertura.caja', 'usuario'])
            ->where('tipo', 'egreso')
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->orderBy('monto', 'desc')
            ->limit(5)
            ->get();

        return [
            'ingresos' => $topIngresos,
            'egresos' => $topEgresos
        ];
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportar(Request $request)
    {
        // Implementar exportación a Excel si es necesario
    }
    // En app/Http/Controllers/ReporteCajaController.php
// Agregar este método después de getTopMovimientos() o antes del dashboard()

    /**
     * Obtener movimientos recientes
     */
    private function getMovimientosRecientes($empresaId, $sucursalId, $fechaInicio, $fechaFin)
    {
        $movimientos = CajaMovimiento::with(['cajaApertura.caja', 'usuario'])
            ->when($empresaId, function ($q) use ($empresaId, $sucursalId) {
                $q->whereHas('cajaApertura', function ($sub) use ($empresaId, $sucursalId) {
                    $sub->whereHas('caja', function ($c) use ($empresaId, $sucursalId) {
                        $c->where('empresa_id', $empresaId);
                        if ($sucursalId) {
                            $c->where('sucursal_id', $sucursalId);
                        }
                    });
                });
            })
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return $movimientos;
    }
}