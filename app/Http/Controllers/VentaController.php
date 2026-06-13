<?php
// app/Http/Controllers/VentaController.php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Credito;
use App\Models\Pagare;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\FormaPago;
use App\Models\TicketConfiguracion;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class VentaController extends Controller
{
    use ActivaTrait;

    private function getCajaAbierta()
    {
        $user = auth()->user();
        $sucursalId = $this->sucursalActivaId();
        $userId = auth()->id();

        $query = CajaApertura::where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta');

        if ($user->hasRole('Vendedor') || $user->hasRole('Cobrador')) {
            // Pueden usar cualquier caja abierta en su sucursal
        } elseif ($user->hasRole('Cajero')) {
            $query->where('user_id', $userId);
        }

        $apertura = $query->first();

        if (!$apertura) {
            throw new \Exception('No hay una caja abierta. Debes abrir una caja primero.');
        }

        return $apertura;
    }

    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();
            $user = auth()->user();

            if (!$empresaId) {
                return redirect()->route('dashboard')->with('error', '⚠️ No hay una empresa activa.');
            }

            if (!$user->can('crear_ventas')) {
                return redirect()->route('dashboard')->with('error', '❌ No tienes permiso para acceder al punto de venta.');
            }

            // 🔥 NUEVA VALIDACIÓN: Verificar cajas abiertas de días anteriores
            $cajasAbiertasAntiguas = $this->validarCajasAbiertasAntiguas($sucursalId, $user);
            
            if ($cajasAbiertasAntiguas['tiene_pendientes']) {
                if ($user->can('cerrar_caja') || $user->hasRole('Super Admin')) {
                    return redirect()->route('cajas.apertura')
                        ->with('warning', '⚠️ ' . $cajasAbiertasAntiguas['mensaje'] . ' Por favor, ciérralas antes de continuar.');
                } else {
                    return redirect()->route('dashboard')
                        ->with('error', '🔒 ' . $cajasAbiertasAntiguas['mensaje'] . ' Solicita al administrador que las cierre.');
                }
            }

            $cajasActivas = collect();
            $cajaAbierta = null;

            // Obtener cajas según rol - SOLO DEL DÍA ACTUAL
            if ($user->hasRole('Vendedor') || $user->hasRole('Cobrador')) {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', today())  // 🔥 SOLO DEL DÍA ACTUAL
                    ->with(['caja', 'usuario'])
                    ->get();
            } elseif ($user->hasRole('Cajero')) {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('user_id', $user->id)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', today())  // 🔥 SOLO DEL DÍA ACTUAL
                    ->with(['caja', 'usuario'])
                    ->get();
            } else {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', today())  // 🔥 SOLO DEL DÍA ACTUAL
                    ->with(['caja', 'usuario'])
                    ->get();
            }

            if ($cajasActivas->isNotEmpty()) {
                $cajaAbierta = $cajasActivas->first();
            }

            if ($cajasActivas->isEmpty()) {
                if ($user->can('abrir_caja')) {
                    return redirect()->route('cajas.apertura')->with('warning', '🔓 Debes abrir una caja para hoy.');
                }
                return redirect()->route('dashboard')->with('error', '🔒 No hay caja abierta para hoy. Solicita al administrador que abra una caja.');
            }

            $productos = Producto::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->where('stock', '>', 0)
                ->with('categoria')
                ->orderBy('nombre')
                ->get();

            $categorias = Categoria::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->get();

            $clientes = Cliente::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $formasPago = FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            return view('ventas.index', compact('productos', 'categorias', 'clientes', 'formasPago', 'cajaAbierta', 'cajasActivas'));

        } catch (\Exception $e) {
            Log::error('Error al cargar punto de venta: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el punto de venta.');
        }
    }

    public function storeContado(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            // Permitir seleccionar caja
            if ($request->has('caja_apertura_id') && $request->caja_apertura_id) {
                $apertura = CajaApertura::findOrFail($request->caja_apertura_id);

                if ($apertura->estado !== 'abierta' || $apertura->sucursal_id !== $this->sucursalActivaId()) {
                    throw new \Exception('La caja seleccionada no es válida.');
                }

                if ($user->hasRole('Cajero') && $apertura->user_id !== $user->id) {
                    throw new \Exception('No tienes permiso para usar esta caja.');
                }
            } else {
                $apertura = $this->getCajaAbierta();
            }

            $items = $request->items;
            $pagos = $request->pagos;
            $incluirIva = $request->incluir_iva ?? false;

            if (empty($items)) {
                throw new \Exception('No hay productos en el carrito');
            }

            if (empty($pagos)) {
                throw new \Exception('Debes registrar al menos una forma de pago');
            }

            $totalPagos = array_sum(array_column($pagos, 'monto'));

            Log::info('=== VENTA CONTADO ===');
            Log::info('Caja apertura ID: ' . $apertura->id);
            Log::info('Items: ' . json_encode($items));
            Log::info('Pagos: ' . json_encode($pagos));
            Log::info('Incluir IVA: ' . ($incluirIva ? 'SI' : 'NO'));

            $subtotal = 0;
            $detalles = [];

            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['id']);

                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
                }

                $producto->reducirStock($item['cantidad'], 'venta', "Venta contado");

                $precioUnitario = $producto->precio_venta;
                $totalItem = $precioUnitario * $item['cantidad'];
                $subtotal += $totalItem;

                $detalles[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $totalItem
                ];
            }

            $iva = $incluirIva ? round($subtotal * 0.16, 2) : 0;
            $total = $incluirIva ? round($subtotal + $iva, 2) : round($subtotal, 2);

            Log::info('Subtotal: ' . $subtotal . ' | IVA: ' . $iva . ' | Total: ' . $total);

            if (abs($totalPagos - $total) > 0.01) {
                throw new \Exception("La suma de los pagos ($totalPagos) no coincide con el total ($total)");
            }

            $venta = Venta::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $this->sucursalActivaId(),
                'caja_apertura_id' => $apertura->id,
                'user_id' => $user->id,
                'cliente_id' => $request->cliente_id ?: null,
                'folio' => Venta::generarFolio(),
                'tipo' => 'contado',
                'estado' => 'completada',
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'observaciones' => $request->observaciones,
                'fecha_venta' => now()
            ]);

            foreach ($detalles as $detalle) {
                $venta->detalles()->create($detalle);
            }

            foreach ($pagos as $pago) {
                $formaPago = FormaPago::findOrFail($pago['forma_pago_id']);

                $venta->pagoDetalles()->create([
                    'forma_pago_id' => $formaPago->id,
                    'monto' => $pago['monto'],
                    'referencia' => $pago['referencia'] ?? null
                ]);

                CajaMovimiento::create([
                    'caja_apertura_id' => $apertura->id,
                    'user_id' => $user->id,
                    'sucursal_id' => $this->sucursalActivaId(),
                    'tipo' => 'ingreso',
                    'categoria' => 'venta',
                    'forma_pago' => $formaPago->clave,
                    'monto' => $pago['monto'],
                    'concepto' => "Venta contado - Folio: {$venta->folio} - {$formaPago->nombre}",
                    'referencia' => $pago['referencia'] ?? $venta->folio
                ]);
            }

            $apertura->increment('total_ingresos', $total);

            $carrito = Carrito::obtenerCarrito();
            $carrito->limpiar();

            DB::commit();

            return response()->json([
                'success' => true,
                'venta_id' => $venta->id,
                'folio' => $venta->folio,
                'message' => 'Venta registrada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar venta contado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeCredito(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            // Permitir seleccionar caja
            if ($request->has('caja_apertura_id') && $request->caja_apertura_id) {
                $apertura = CajaApertura::findOrFail($request->caja_apertura_id);

                if ($apertura->estado !== 'abierta' || $apertura->sucursal_id !== $this->sucursalActivaId()) {
                    throw new \Exception('La caja seleccionada no es válida.');
                }

                if ($user->hasRole('Cajero') && $apertura->user_id !== $user->id) {
                    throw new \Exception('No tienes permiso para usar esta caja.');
                }
            } else {
                $apertura = $this->getCajaAbierta();
            }

            $items = $request->items;

            if (empty($items)) {
                throw new \Exception('No hay productos en el carrito');
            }

            if (!$request->cliente_id) {
                throw new \Exception('Debes seleccionar un cliente para la venta a crédito');
            }

            $subtotal = 0;
            $detalles = [];

            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['id']);

                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
                }

                $producto->reducirStock($item['cantidad'], 'venta', "Venta a crédito");

                $precioUnitario = $producto->precio_venta;
                $totalItem = $precioUnitario * $item['cantidad'];
                $subtotal += $totalItem;

                $detalles[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $totalItem
                ];
            }

            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;

            $venta = Venta::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $this->sucursalActivaId(),
                'caja_apertura_id' => $apertura->id,
                'user_id' => $user->id,
                'cliente_id' => $request->cliente_id,
                'folio' => Venta::generarFolio(),
                'tipo' => 'credito',
                'estado' => 'completada',
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'observaciones' => $request->observaciones,
                'fecha_venta' => now()
            ]);

            foreach ($detalles as $detalle) {
                $venta->detalles()->create($detalle);
            }

            $plazosDias = [
                '7_dias' => 7,
                '15_dias' => 15,
                '1_mes' => 30,
                '2_meses' => 60,
                '3_meses' => 90,
                '6_meses' => 180,
                '1_ano' => 365
            ];

            $dias = $plazosDias[$request->plazo];
            $numPagos = intval($request->num_pagos);
            $montoPorPago = $total / $numPagos;

            $credito = Credito::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $this->sucursalActivaId(),
                'venta_id' => $venta->id,
                'cliente_id' => $request->cliente_id,
                'user_id' => $user->id,
                'monto_total' => $total,
                'monto_pagado' => 0,
                'saldo_pendiente' => $total,
                'plazo' => $request->plazo,
                'num_pagos' => $numPagos,
                'estado' => 'activo',
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addDays($dias)
            ]);

            $intervaloDias = $dias / $numPagos;

            for ($i = 1; $i <= $numPagos; $i++) {
                Pagare::create([
                    'credito_id' => $credito->id,
                    'folio' => Pagare::generarFolio(),
                    'numero_pago' => $i,
                    'monto' => $montoPorPago,
                    'fecha_vencimiento' => now()->addDays(round($intervaloDias * $i)),
                    'estado' => 'pendiente'
                ]);
            }

            $carrito = Carrito::obtenerCarrito();
            $carrito->limpiar();

            DB::commit();

            return response()->json([
                'success' => true,
                'venta_id' => $venta->id,
                'credito_id' => $credito->id,
                'folio' => $venta->folio,
                'message' => 'Venta a crédito registrada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar venta crédito: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function ticket($id)
    {
        try {
            $venta = Venta::with(['detalles.producto', 'usuario', 'cliente'])->findOrFail($id);

            $config = TicketConfiguracion::where('empresa_id', $venta->empresa_id)
                ->where('tipo', 'movimiento')
                ->where('activo', true)
                ->first();

            if (!$config) {
                $config = new \stdClass();
                $config->nombre_empresa = optional($venta->empresa)->nombre ?? 'Mi Empresa';
                $config->rfc = optional($venta->empresa)->rfc ?? '';
                $config->direccion = optional($venta->empresa)->direccion ?? '';
                $config->telefono = optional($venta->empresa)->telefono ?? '';
                $config->email = optional($venta->empresa)->correo ?? '';
                $config->footer = '¡Gracias por su compra!';
                $config->mostrar_rfc = true;
                $config->mostrar_direccion = true;
                $config->mostrar_telefono = true;
                $config->mostrar_email = true;
                $config->ancho_papel = '80mm';
                $config->fuente = 'monospace';
                $config->tamano_fuente = 12;
                $config->auto_imprimir = true;
                $config->copias = 1;
            }

            $contenido = '
            <div class="row"><span>Atiende:</span><span>' . $venta->usuario->name . '</span></div>
            ' . ($venta->cliente ? '<div class="row"><span>Cliente:</span><span>' . $venta->cliente->nombre . '</span></div>' : '') . '
            <div class="divider"></div>';

            foreach ($venta->detalles as $detalle) {
                $contenido .= '<div class="row"><span>' . $detalle->cantidad . 'x ' . $detalle->producto->nombre . '</span><span>$' . number_format($detalle->subtotal, 2) . '</span></div>';
            }

            $contenido .= '
            <div class="divider"></div>
            <div class="row"><span>Subtotal:</span><span>$' . number_format($venta->subtotal, 2) . '</span></div>
            <div class="row"><span>IVA (16%):</span><span>$' . number_format($venta->iva, 2) . '</span></div>
            <div class="monto neutro">TOTAL: $' . number_format($venta->total, 2) . '</div>
            ' . ($venta->tipo == 'credito' ? '<div class="status status-warning">VENTA A CRÉDITO</div>' : '');

            return view('tickets.base', [
                'config' => $config,
                'titulo' => 'TICKET DE VENTA',
                'numero' => $venta->folio,
                'fecha' => $venta->fecha_venta->format('d/m/Y'),
                'fecha_hora' => $venta->fecha_venta->format('H:i:s'),
                'contenido' => $contenido,
                'auto_imprimir' => $config->auto_imprimir ?? true,
                'copias' => $config->copias ?? 1
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar ticket: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el ticket.');
        }
    }

    public function imprimirPagares($creditoId)
    {
        try {
            $credito = Credito::with(['pagares', 'cliente', 'venta'])->findOrFail($creditoId);
            $pdf = Pdf::loadView('ventas.pagares', compact('credito'));
            $pdf->setPaper('letter', 'portrait');
            return $pdf->download("Pagares_Credito_{$credito->id}.pdf");
        } catch (\Exception $e) {
            Log::error('Error al imprimir pagarés: ' . $e->getMessage());
            return back()->with('error', 'Error al generar los pagarés');
        }
    }

    public function historial()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();
            $user = auth()->user();

            $ventas = Venta::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->with(['usuario', 'cliente'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $cajasActivas = collect();
            $cajaAbierta = null;

            if ($user->hasRole('Vendedor') || $user->hasRole('Cobrador')) {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')->with(['caja', 'usuario'])->get();
            } elseif ($user->hasRole('Cajero')) {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('user_id', $user->id)->where('estado', 'abierta')->with(['caja', 'usuario'])->get();
            } else {
                $cajasActivas = CajaApertura::where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')->with(['caja', 'usuario'])->get();
            }

            if ($cajasActivas->isNotEmpty()) {
                $cajaAbierta = $cajasActivas->first();
            }

            return view('ventas.historial', compact('ventas', 'cajaAbierta', 'cajasActivas'));

        } catch (\Exception $e) {
            Log::error('Error al cargar historial: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el historial');
        }
    }

    public function show($id)
    {
        try {
            $venta = Venta::with(['detalles.producto', 'usuario', 'cliente', 'credito.pagares'])->findOrFail($id);
            return view('ventas.show', compact('venta'));
        } catch (\Exception $e) {
            Log::error('Error al ver venta: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la venta');
        }
    }

    public function cancelar($id)
    {
        try {
            DB::beginTransaction();

            $venta = Venta::findOrFail($id);

            if ($venta->estado === 'cancelada') {
                return response()->json([
                    'success' => false,
                    'icon' => 'warning',
                    'message' => 'La venta ya está cancelada'
                ], 400);
            }

            foreach ($venta->detalles as $detalle) {
                $detalle->producto->increment('stock', $detalle->cantidad);
            }

            $venta->estado = 'cancelada';
            $venta->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'message' => "Venta {$venta->folio} cancelada correctamente. Stock restaurado."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar venta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error al cancelar la venta: ' . $e->getMessage()
            ], 500);
        }
    }
    private function validarCajasAbiertasAntiguas($sucursalId, $user)
    {
        $query = CajaApertura::where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta')
            ->whereDate('fecha', '<', today());  // Fechas anteriores a hoy

        // Filtrar por usuario según rol
        if (!$user->hasRole('Super Admin')) {
            $query->where('user_id', $user->id);
        }

        $cajasAntiguas = $query->with(['caja', 'usuario'])->get();

        if ($cajasAntiguas->isEmpty()) {
            return [
                'tiene_pendientes' => false,
                'mensaje' => '',
                'cajas' => collect()
            ];
        }

        $fechas = $cajasAntiguas->pluck('fecha')->map(function ($fecha) {
            return $fecha->format('d/m/Y');
        })->implode(', ');

        $mensaje = 'Existen cajas abiertas de días anteriores (' . $fechas . ').';

        return [
            'tiene_pendientes' => true,
            'mensaje' => $mensaje,
            'cajas' => $cajasAntiguas
        ];
    }
}