<?php
// app/Http/Controllers/RespaldoController.php

namespace App\Http\Controllers;

use App\Exports\RespaldoEmpresaExport;
use App\Imports\RespaldoEmpresaImport;
use App\Models\Empresa;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RespaldoController extends Controller
{
    use ActivaTrait;

    /**
     * Vista principal de respaldos
     */
    public function index()
    {
        $empresaId = $this->empresaActivaId();
        $empresa = Empresa::find($empresaId);

        return view('respaldos.index', compact('empresa'));
    }

    /**
     * Exportar respaldo completo de la empresa a Excel
     */
    public function exportarExcel()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = Empresa::find($empresaId);

            if (!$empresaId) {
                return back()->with('error', 'No hay una empresa activa.');
            }

            $nombreEmpresa = preg_replace('/[^a-zA-Z0-9]/', '_', $empresa->nombre ?? 'empresa');
            $fecha = now()->format('Y-m-d_His');
            $filename = "respaldo_{$nombreEmpresa}_{$fecha}.xlsx";

            // Usar el facade Excel
            return \Maatwebsite\Excel\Facades\Excel::download(new RespaldoEmpresaExport($empresaId), $filename);

        } catch (\Exception $e) {
            Log::error('Error al exportar respaldo: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el respaldo: ' . $e->getMessage());
        }
    }

    /**
     * Generar respaldo SQL (estructura y datos)
     */
    public function exportarSQL()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = Empresa::find($empresaId);

            if (!$empresaId) {
                return back()->with('error', 'No hay una empresa activa.');
            }

            // Tablas a exportar (solo las que tienen campo empresa_id)
            $tablas = [
                'clientes',
                'productos',
                'insumos',
                'proveedors',
                'sucursals',
                'categorias',
                'unidad_medidas',
                'formas_pago',
                'ventas',
                'venta_detalles',
                'creditos',
                'pagares',
                'cobranzas',
                'cajas',
                'caja_aperturas',
                'caja_movimientos',
                'caja_arqueos',
                'caja_transferencias',
                'cotizacions',
                'cotizacion_detalles',
                'inventario_movimientos',
                'producto_insumos',
                'producto_proveedors',
                'ticket_configuracions'
            ];

            $sql = "-- Respaldo de la empresa: {$empresa->nombre}\n";
            $sql .= "-- RFC: {$empresa->rfc}\n";
            $sql .= "-- Fecha: " . now()->format('Y-m-d H:i:s') . "\n";
            $sql .= "-- Empresa ID: {$empresaId}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tablas as $tabla) {
                // Verificar si la tabla existe
                if (!Schema::hasTable($tabla)) {
                    continue;
                }

                // Obtener estructura de la tabla
                $createTable = DB::select("SHOW CREATE TABLE {$tabla}");
                if (!empty($createTable)) {
                    $sql .= "-- Estructura de tabla: {$tabla}\n";
                    $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                }

                // Obtener datos filtrados por empresa_id
                if (Schema::hasColumn($tabla, 'empresa_id')) {
                    $datos = DB::table($tabla)->where('empresa_id', $empresaId)->get();
                } elseif ($tabla === 'sucursals') {
                    $datos = DB::table($tabla)->where('empresa_id', $empresaId)->get();
                } elseif ($tabla === 'caja_aperturas') {
                    $datos = DB::table($tabla)->where('empresa_id', $empresaId)->get();
                } elseif ($tabla === 'venta_detalles') {
                    // Venta detalles se filtra por ventas de la empresa
                    $ventasIds = DB::table('ventas')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('venta_id', $ventasIds)->get();
                } elseif ($tabla === 'cotizacion_detalles') {
                    $cotizacionesIds = DB::table('cotizacions')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('cotizacion_id', $cotizacionesIds)->get();
                } elseif ($tabla === 'pagares') {
                    $creditosIds = DB::table('creditos')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('credito_id', $creditosIds)->get();
                } elseif ($tabla === 'cobranzas') {
                    $datos = DB::table($tabla)->where('empresa_id', $empresaId)->get();
                } elseif ($tabla === 'caja_movimientos') {
                    $aperturasIds = DB::table('caja_aperturas')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('caja_apertura_id', $aperturasIds)->get();
                } elseif ($tabla === 'caja_arqueos') {
                    $aperturasIds = DB::table('caja_aperturas')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('caja_apertura_id', $aperturasIds)->get();
                } elseif ($tabla === 'caja_transferencias') {
                    $cajasIds = DB::table('cajas')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('caja_origen_id', $cajasIds)
                        ->orWhereIn('caja_destino_id', $cajasIds)->get();
                } elseif ($tabla === 'producto_insumos') {
                    $productosIds = DB::table('productos')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('producto_id', $productosIds)->get();
                } elseif ($tabla === 'producto_proveedors') {
                    $productosIds = DB::table('productos')->where('empresa_id', $empresaId)->pluck('id');
                    $datos = DB::table($tabla)->whereIn('producto_id', $productosIds)->get();
                } elseif ($tabla === 'inventario_movimientos') {
                    $datos = DB::table($tabla)->where('empresa_id', $empresaId)->get();
                } elseif ($tabla === 'ticket_configuracions') {
                    $datos = DB::table($tabla)->where('empresa_id', $empresaId)->get();
                } else {
                    continue;
                }

                if ($datos->count() > 0) {
                    $sql .= "-- Datos de tabla: {$tabla}\n";
                    foreach ($datos as $fila) {
                        $filaArray = (array) $fila;
                        unset($filaArray['empresa_id']); // Quitar empresa_id para evitar conflictos
                        $columnas = array_keys($filaArray);
                        $valores = array_map(function ($v) {
                            return is_null($v) ? 'NULL' : DB::getPdo()->quote($v);
                        }, array_values($filaArray));

                        $sql .= "INSERT INTO `{$tabla}` (`" . implode('`, `', $columnas) . "`) VALUES (" . implode(', ', $valores) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $nombreEmpresa = preg_replace('/[^a-zA-Z0-9]/', '_', $empresa->nombre ?? 'empresa');
            $filename = "respaldo_{$nombreEmpresa}_" . now()->format('Y-m-d_His') . ".sql";

            return response($sql, 200)
                ->header('Content-Type', 'application/sql')
                ->header('Content-Disposition', "attachment; filename=\"$filename\"");

        } catch (\Exception $e) {
            Log::error('Error al exportar SQL: ' . $e->getMessage());
            return back()->with('error', 'Error al generar respaldo SQL: ' . $e->getMessage());
        }
    }

    /**
     * Importar desde archivo Excel
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $empresaId = $this->empresaActivaId();

            if (!$empresaId) {
                return back()->with('error', 'No hay una empresa activa.');
            }

            $import = new RespaldoEmpresaImport($empresaId);
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('archivo'));

            $mensajes = $import->getMensajes();

            return back()->with('success', 'Importación completada: ' . implode(' ', $mensajes));

        } catch (\Exception $e) {
            Log::error('Error al importar respaldo: ' . $e->getMessage());
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }
}