<?php
// app/Http/Controllers/CobranzaController.php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Pagare;
use App\Models\Cobranza;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Models\FormaPago;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CobranzaController extends Controller
{
    use ActivaTrait;
    
    // Los métodos empresaActivaId y sucursalActivaId ya vienen del Trait ActivaTrait
    // NO los declares aquí

    private function getCajaAbierta()
    {
        $sucursalId = $this->sucursalActivaId();
        $userId = auth()->id();

        $apertura = CajaApertura::where('sucursal_id', $sucursalId)
            ->where('user_id', $userId)
            ->where('estado', 'abierta')
            ->first();

        if (!$apertura) {
            throw new \Exception('No tienes una caja abierta. Debes abrir una caja primero.');
        }

        return $apertura;
    }

    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $creditos = Credito::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->with([
                    'cliente',
                    'venta',
                    'pagares' => function ($q) {
                        $q->orderBy('fecha_vencimiento');
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $formasPago = FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            return view('cobranza.index', compact('creditos', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al cargar cobranza: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la cobranza.');
        }
    }

    public function show($id)
    {
        try {
            $credito = Credito::with(['cliente', 'venta', 'pagares', 'cobranzas.usuario'])
                ->findOrFail($id);

            $formasPago = FormaPago::where('empresa_id', $this->empresaActivaId())
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            return view('cobranza.show', compact('credito', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al ver crédito: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el crédito.');
        }
    }

    public function registrarAbono(Request $request)
    {
        try {
            DB::beginTransaction();

            $apertura = $this->getCajaAbierta();

            $validated = $request->validate([
                'credito_id' => 'required|exists:creditos,id',
                'monto' => 'required|numeric|min:0.01',
                'forma_pago' => 'required|string',
                'referencia' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string'
            ]);

            $credito = Credito::findOrFail($validated['credito_id']);

            if ($credito->estado === 'pagado') {
                throw new \Exception('Este crédito ya está pagado.');
            }

            if ($validated['monto'] > $credito->saldo_pendiente) {
                throw new \Exception("El monto excede el saldo pendiente. Saldo actual: $" . number_format($credito->saldo_pendiente, 2));
            }

            $pagares = $credito->pagares()->where('estado', 'pendiente')
                ->orderBy('fecha_vencimiento')
                ->get();

            $montoRestante = $validated['monto'];

            foreach ($pagares as $pagare) {
                if ($montoRestante <= 0) break;

                if ($montoRestante >= $pagare->monto) {
                    $pagare->update([
                        'estado' => 'pagado',
                        'fecha_pago' => now()
                    ]);
                    $montoRestante -= $pagare->monto;
                } else {
                    $montoRestante = 0;
                }
            }

            $cobranza = Cobranza::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $this->sucursalActivaId(),
                'credito_id' => $credito->id,
                'user_id' => auth()->id(),
                'caja_movimiento_id' => null,
                'monto' => $validated['monto'],
                'tipo' => 'abono',
                'observaciones' => $validated['observaciones'],
                'fecha_cobro' => now()
            ]);

            $nuevoPagado = $credito->monto_pagado + $validated['monto'];
            $nuevoSaldo = $credito->saldo_pendiente - $validated['monto'];

            $credito->update([
                'monto_pagado' => $nuevoPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'activo'
            ]);

            $movimiento = CajaMovimiento::create([
                'caja_apertura_id' => $apertura->id,
                'user_id' => auth()->id(),
                'sucursal_id' => $this->sucursalActivaId(),
                'tipo' => 'ingreso',
                'categoria' => 'abono_credito',
                'forma_pago' => $validated['forma_pago'],
                'monto' => $validated['monto'],
                'concepto' => "Abono a crédito - Venta: {$credito->venta->folio} - Cliente: {$credito->cliente->nombre}",
                'referencia' => $validated['referencia'] ?? null
            ]);

            $cobranza->update(['caja_movimiento_id' => $movimiento->id]);
            $apertura->increment('total_ingresos', $validated['monto']);

            DB::commit();

            $mensaje = "Abono registrado correctamente. Nuevo saldo: $" . number_format($nuevoSaldo, 2);
            if ($nuevoSaldo <= 0) {
                $mensaje = "¡Crédito liquidado! Abono registrado correctamente.";
            }

            return redirect()->route('cobranza.show', $credito->id)
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar abono: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function pagarPagare(Request $request, $pagareId)
    {
        try {
            DB::beginTransaction();

            $apertura = $this->getCajaAbierta();

            $validated = $request->validate([
                'forma_pago' => 'required|string',
                'referencia' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string'
            ]);

            $pagare = Pagare::with('credito')->findOrFail($pagareId);
            $credito = $pagare->credito;

            if ($pagare->estado !== 'pendiente') {
                throw new \Exception('Este pagaré ya fue pagado o está vencido.');
            }

            $cobranza = Cobranza::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $this->sucursalActivaId(),
                'credito_id' => $credito->id,
                'pagare_id' => $pagare->id,
                'user_id' => auth()->id(),
                'caja_movimiento_id' => null,
                'monto' => $pagare->monto,
                'tipo' => 'pago_pagare',
                'observaciones' => $validated['observaciones'],
                'fecha_cobro' => now()
            ]);

            $pagare->update([
                'estado' => 'pagado',
                'fecha_pago' => now()
            ]);

            $nuevoPagado = $credito->monto_pagado + $pagare->monto;
            $nuevoSaldo = $credito->saldo_pendiente - $pagare->monto;

            $credito->update([
                'monto_pagado' => $nuevoPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'activo'
            ]);

            $movimiento = CajaMovimiento::create([
                'caja_apertura_id' => $apertura->id,
                'user_id' => auth()->id(),
                'sucursal_id' => $this->sucursalActivaId(),
                'tipo' => 'ingreso',
                'categoria' => 'abono_credito',
                'forma_pago' => $validated['forma_pago'],
                'monto' => $pagare->monto,
                'concepto' => "Pago de pagaré #{$pagare->folio} - Venta: {$credito->venta->folio} - Cliente: {$credito->cliente->nombre}",
                'referencia' => $validated['referencia'] ?? null
            ]);

            $cobranza->update(['caja_movimiento_id' => $movimiento->id]);
            $apertura->increment('total_ingresos', $pagare->monto);

            DB::commit();

            $mensaje = "Pagaré #{$pagare->folio} pagado correctamente.";
            if ($nuevoSaldo <= 0) {
                $mensaje = "¡Crédito liquidado! " . $mensaje;
            }

            return redirect()->route('cobranza.show', $credito->id)
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al pagar pagaré: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function historialPagos($creditoId)
    {
        try {
            $credito = Credito::findOrFail($creditoId);
            $cobranzas = Cobranza::where('credito_id', $creditoId)
                ->with('usuario')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'cobranzas' => $cobranzas->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'fecha' => $c->fecha_cobro->format('d/m/Y H:i'),
                        'monto' => $c->monto,
                        'tipo' => $c->tipo,
                        'usuario' => $c->usuario->name,
                        'observaciones' => $c->observaciones
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function historialGeneral()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $cobranzas = Cobranza::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->with(['credito.cliente', 'usuario'])
                ->orderBy('created_at', 'desc')
                ->paginate(30);

            return view('cobranza.historial', compact('cobranzas'));
        } catch (\Exception $e) {
            Log::error('Error al cargar historial de cobranza: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el historial.');
        }
    }

    public function condonaciones()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            if (auth()->user()->hasRole('Super Admin') && !$sucursalId) {
                $sucursal = \App\Models\Sucursal::where('empresa_id', $empresaId)
                    ->where('activo', true)
                    ->first();
                if ($sucursal) {
                    $sucursalId = $sucursal->id;
                    session(['sucursal_activa_id' => $sucursalId]);
                }
            }

            if (!$sucursalId) {
                return redirect()->route('dashboard')
                    ->with('error', '⚠️ Selecciona una sucursal antes de continuar.');
            }

            $creditosVencidos = Credito::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', 'activo')
                ->whereHas('pagares', function ($q) {
                    $q->where('estado', 'pendiente')
                        ->where('fecha_vencimiento', '<', now());
                })
                ->with([
                    'cliente',
                    'pagares' => function ($q) {
                        $q->where('estado', 'pendiente')
                            ->where('fecha_vencimiento', '<', now())
                            ->orderBy('fecha_vencimiento');
                    }
                ])
                ->get();

            $formasPago = FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            return view('cobranza.condonaciones', compact('creditosVencidos', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al cargar condonaciones: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las condonaciones: ' . $e->getMessage());
        }
    }

    public function condonarAdeudo(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'pagare_id' => 'required|exists:pagares,id',
                'motivo' => 'required|string|max:500',
                'autorizado_por' => 'required|string'
            ]);

            $pagare = Pagare::with('credito')->findOrFail($validated['pagare_id']);
            $credito = $pagare->credito;

            if ($pagare->estado !== 'pendiente') {
                throw new \Exception('Este pagaré ya no está pendiente.');
            }

            $cobranza = Cobranza::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $this->sucursalActivaId(),
                'credito_id' => $credito->id,
                'pagare_id' => $pagare->id,
                'user_id' => auth()->id(),
                'caja_movimiento_id' => null,
                'monto' => $pagare->monto,
                'tipo' => 'condonacion',
                'observaciones' => "CONDONADO - Motivo: {$validated['motivo']} - Autorizado por: {$validated['autorizado_por']}",
                'fecha_cobro' => now()
            ]);

            $pagare->update([
                'estado' => 'condonado',
                'fecha_pago' => now()
            ]);

            $nuevoPagado = $credito->monto_pagado + $pagare->monto;
            $nuevoSaldo = $credito->saldo_pendiente - $pagare->monto;

            $credito->update([
                'monto_pagado' => $nuevoPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'activo'
            ]);

            DB::commit();

            return redirect()->route('cobranza.condonaciones')
                ->with('success', "Pagaré #{$pagare->folio} condonado correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al condonar adeudo: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
}