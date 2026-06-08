<?php
// app/Http/Controllers/CajaController.php
namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaArqueo;
use App\Models\CajaMovimiento;
use App\Models\CajaTransferencia;
use App\Models\Sucursal;
use App\Services\CajaService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CajaController extends Controller
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
            // Intentar obtener de la sesión
            $sucursalId = session('sucursal_activa_id');

            // Si no hay sucursal en sesión, obtener la primera sucursal activa de la empresa
            if (!$sucursalId) {
                $sucursal = Sucursal::where('empresa_id', $this->empresaActivaId())
                    ->where('activo', true)
                    ->first();

                if ($sucursal) {
                    $sucursalId = $sucursal->id;
                    session(['sucursal_activa_id' => $sucursalId]);
                }
            }

            // Si aún no hay, intentar obtener de la caja abierta del usuario
            if (!$sucursalId) {
                $apertura = CajaApertura::where('user_id', auth()->id())
                    ->where('estado', 'abierta')
                    ->first();

                if ($apertura) {
                    $sucursalId = $apertura->sucursal_id;
                    session(['sucursal_activa_id' => $sucursalId]);
                }
            }

            return $sucursalId;
        }

        return auth()->user()->sucursal_id;
    }

    // ==================== CAJAS ====================

    public function indexCajas()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $cajas = Caja::where('empresa_id', $empresaId)
                ->when($sucursalId, function ($q) use ($sucursalId) {
                    return $q->where('sucursal_id', $sucursalId);
                })
                ->with(['sucursal', 'aperturaActual'])
                ->orderBy('nombre')
                ->paginate(10);

            return view('cajas.index', compact('cajas'));
        } catch (\Exception $e) {
            Log::error('Error al listar cajas: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las cajas.');
        }
    }

    public function createCaja()
    {
        try {
            $sucursales = Sucursal::where('empresa_id', $this->empresaActivaId())
                ->where('activo', true)
                ->get();

            return view('cajas.create-caja', compact('sucursales'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    public function storeCaja(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'permite_multiple' => 'boolean',
            'activo' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $caja = Caja::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $validated['sucursal_id'],
                'nombre' => $validated['nombre'],
                'codigo' => Caja::generarCodigo(),
                'descripcion' => $validated['descripcion'] ?? null,
                'permite_multiple' => $request->has('permite_multiple'),
                'activo' => $request->has('activo'),
            ]);

            DB::commit();
            return redirect()->route('cajas.cajas.index')
                ->with('success', 'Caja "' . $caja->nombre . '" creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear caja: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al crear la caja.');
        }
    }

    public function editCaja(Caja $caja)
    {
        try {
            $sucursales = Sucursal::where('empresa_id', $this->empresaActivaId())
                ->where('activo', true)
                ->get();

            return view('cajas.edit', compact('caja', 'sucursales'));
        } catch (\Exception $e) {
            Log::error('Error al cargar edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    public function updateCaja(Request $request, Caja $caja)
    {
        $validated = $request->validate(
            [
                'sucursal_id' => 'required|exists:sucursals,id',
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'permite_multiple' => 'boolean',
                'activo' => 'boolean',
            ],
            [
                // sucursal_id
                'sucursal_id.required' => 'La sucursal es obligatoria.',
                'sucursal_id.exists' => 'La sucursal seleccionada no existe.',

                // nombre
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.string' => 'El nombre debe ser un texto válido.',
                'nombre.max' => 'El nombre no puede superar los 100 caracteres.',

                // descripcion
                'descripcion.string' => 'La descripción debe ser un texto válido.',

                // booleanos
                'permite_multiple.boolean' => 'El campo "permite múltiple" debe ser verdadero o falso.',
                'activo.boolean' => 'El campo "activo" debe ser verdadero o falso.',
            ]
        );

        DB::beginTransaction();
        try {
            $caja->update([
                'sucursal_id' => $validated['sucursal_id'],
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'permite_multiple' => $request->has('permite_multiple'),
                'activo' => $request->has('activo'),
            ]);

            DB::commit();
            return redirect()->route('cajas.cajas.index')
                ->with('success', 'Caja "' . $caja->nombre . '" actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar caja: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la caja.');
        }
    }

    // ==================== APERTURA DE CAJA ====================

    public function aperturaIndex()
    {
        try {
            $sucursalId = $this->sucursalActivaId();
            $userId = auth()->id();

            // Verificar si hay una caja abierta
            $aperturaActual = CajaApertura::where('sucursal_id', $sucursalId)
                ->where('user_id', $userId)
                ->where('estado', 'abierta')
                ->first();

            // Verificar cajas abiertas de días anteriores
            $aperturaAnterior = CajaApertura::where('sucursal_id', $sucursalId)
                ->where('user_id', $userId)
                ->where('estado', 'abierta')
                ->whereDate('fecha', '<', today())
                ->first();

            $cajasDisponibles = Caja::where('empresa_id', $this->empresaActivaId())
                ->where('sucursal_id', $sucursalId)
                ->where('activo', true)
                ->get();

            return view('cajas.apertura', compact('aperturaActual', 'aperturaAnterior', 'cajasDisponibles'));
        } catch (\Exception $e) {
            Log::error('Error al cargar apertura: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la apertura de caja.');
        }
    }

    public function abrirCaja(Request $request)
    {
        $validated = $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'monto_inicial' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $apertura = CajaService::abrirCaja(
                $validated['caja_id'],
                auth()->id(),
                $this->sucursalActivaId(),
                $validated['monto_inicial'],
                $validated['observaciones'] ?? null
            );

            return redirect()->route('cajas.operaciones')
                ->with('success', 'Caja abierta correctamente. ID de apertura: ' . $apertura->id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cerrarCaja(Request $request)
    {
        $validated = $request->validate([
            'apertura_id' => 'required|exists:caja_aperturas,id',
            'monto_final' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $apertura = CajaApertura::findOrFail($validated['apertura_id']);

            // Verificar retiros pendientes de autorización
            $retirosPendientes = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->where('tipo', 'egreso')
                ->where('categoria', 'retiro_parcial')
                ->where('requiere_autorizacion', true)
                ->whereNull('autorizado_por')
                ->sum('monto');

            if ($retirosPendientes > 0) {
                return back()->with('error', "Hay retiros pendientes de autorización por $$retirosPendientes. Debes autorizarlos antes de cerrar la caja.");
            }

            // Calcular saldo esperado
            $saldoEsperado = $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos;

            // Si el monto final es menor al saldo esperado, se debe retirar la diferencia
            if ($validated['monto_final'] < $saldoEsperado) {
                $diferencia = $saldoEsperado - $validated['monto_final'];

                // Registrar retiro final
                $movimiento = CajaMovimiento::create([
                    'caja_apertura_id' => $apertura->id,
                    'user_id' => auth()->id(),
                    'sucursal_id' => $apertura->sucursal_id,
                    'tipo' => 'egreso',
                    'categoria' => 'retiro_final',
                    'forma_pago' => 'efectivo',
                    'monto' => $diferencia,
                    'concepto' => "RETIRO FINAL DE CIERRE DE CAJA",
                    'referencia' => "CIERRE-{$apertura->id}",
                    'requiere_autorizacion' => false
                ]);

                // Actualizar totales de la apertura
                $apertura->increment('total_egresos', $diferencia);
            }

            // Cerrar la caja
            CajaService::cerrarCaja(
                $validated['apertura_id'],
                $validated['monto_final'],
                $validated['observaciones'] ?? null
            );

            DB::commit();

            return redirect()->route('cajas.apertura')
                ->with('success', 'Caja cerrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== MOVIMIENTOS ====================

    public function operaciones()
    {
        try {
            $sucursalId = $this->sucursalActivaId();
            $userId = auth()->id();

            $apertura = CajaApertura::where('sucursal_id', $sucursalId)
                ->where('user_id', $userId)
                ->where('estado', 'abierta')
                ->first();

            if (!$apertura) {
                return redirect()->route('cajas.apertura')
                    ->with('error', 'No tienes una caja abierta. Debes abrir una caja primero.');
            }

            $movimientos = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $resumen = CajaService::resumenDia($apertura->id);

            // Obtener formas de pago activas
            $empresaId = $this->empresaActivaId();
            $formasPago = \App\Models\FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            return view('cajas.operaciones', compact('apertura', 'movimientos', 'resumen', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al cargar operaciones: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las operaciones.');
        }
    }

    public function registrarMovimiento(Request $request)
    {
        $validated = $request->validate([
            'apertura_id' => 'required|exists:caja_aperturas,id',
            'tipo' => 'required|in:ingreso,egreso',
            'categoria' => 'required|in:venta,abono_credito,cobro_servicio,prestamo,compra,gasto,retiro,transferencia,ajuste',
            'forma_pago' => 'required|in:efectivo,tarjeta_debito,tarjeta_credito,vale,transferencia,cheque',
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'required|string|max:500',
            'referencia' => 'nullable|string|max:100',
            'requiere_autorizacion' => 'boolean',
        ]);

        try {
            $movimiento = CajaService::registrarMovimiento(
                $validated['apertura_id'],
                auth()->id(),
                $validated,
                $request->has('requiere_autorizacion')
            );

            $mensaje = 'Movimiento registrado correctamente.';
            if ($movimiento->requiere_autorizacion) {
                $mensaje = 'Movimiento registrado. Requiere autorización de un administrador.';
            }

            return redirect()->route('cajas.operaciones')
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== AUTORIZACIONES ====================

    public function autorizacionesPendientes()
    {
        try {
            $sucursalId = $this->sucursalActivaId();

            $movimientosPendientes = CajaMovimiento::where('requiere_autorizacion', true)
                ->whereNull('autorizado_por')
                ->with(['cajaApertura', 'usuario'])
                ->orderBy('created_at')
                ->paginate(20);

            $transferenciasPendientes = CajaTransferencia::where('estado', 'pendiente')
                ->with(['cajaOrigen', 'cajaDestino', 'usuario'])
                ->orderBy('created_at')
                ->paginate(20);

            return view('cajas.autorizaciones', compact('movimientosPendientes', 'transferenciasPendientes'));
        } catch (\Exception $e) {
            Log::error('Error al cargar autorizaciones: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las autorizaciones.');
        }
    }

    public function autorizarMovimiento(Request $request, $movimientoId)
    {
        $validated = $request->validate([
            'password_maestra' => 'required|string',
        ]);

        try {
            CajaService::autorizarMovimiento(
                $movimientoId,
                auth()->id(),
                $validated['password_maestra']
            );

            return redirect()->route('cajas.autorizaciones')
                ->with('success', 'Movimiento autorizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== TRANSFERENCIAS ====================

    public function transferencias()
    {
        try {
            $sucursalId = $this->sucursalActivaId();

            $cajas = Caja::where('empresa_id', $this->empresaActivaId())
                ->where('sucursal_id', $sucursalId)
                ->where('activo', true)
                ->get();

            return view('cajas.transferencias', compact('cajas'));
        } catch (\Exception $e) {
            Log::error('Error al cargar transferencias: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las transferencias.');
        }
    }

    public function solicitarTransferencia(Request $request)
    {
        $validated = $request->validate([
            'caja_origen_id' => 'required|exists:cajas,id',
            'caja_destino_id' => 'required|exists:cajas,id|different:caja_origen_id',
            'monto' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:500',
        ]);

        try {
            $transferencia = CajaService::transferirEntreCajas(
                $validated['caja_origen_id'],
                $validated['caja_destino_id'],
                auth()->id(),
                $validated['monto'],
                $validated['motivo']
            );

            return redirect()->route('cajas.autorizaciones')
                ->with('success', 'Solicitud de transferencia creada. Esperando autorización.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function autorizarTransferencia(Request $request, $transferenciaId)
    {
        $validated = $request->validate([
            'password_maestra' => 'required|string',
        ]);

        try {
            CajaService::aprobarTransferencia(
                $transferenciaId,
                auth()->id(),
                $validated['password_maestra']
            );

            return redirect()->route('cajas.autorizaciones')
                ->with('success', 'Transferencia autorizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== REPORTES ====================

    public function reporteDia($aperturaId)
    {
        try {
            $empresaId = $this->empresaActivaId();

            $resumen = CajaService::resumenDia($aperturaId);
            $apertura = CajaApertura::with(['caja', 'usuario', 'movimientos', 'sucursal'])->findOrFail($aperturaId);

            // Obtener formas de pago activas para mostrar iconos
            $formasPago = \App\Models\FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            return view('cajas.reporte-dia', compact('resumen', 'apertura', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al generar reporte: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }

    // ==================== ARQUEOS ====================

    public function arqueos()
    {
        try {
            $sucursalId = $this->sucursalActivaId();
            $userId = auth()->id();

            $apertura = CajaApertura::where('sucursal_id', $sucursalId)
                ->where('user_id', $userId)
                ->where('estado', 'abierta')
                ->first();

            if (!$apertura) {
                return redirect()->route('cajas.apertura')
                    ->with('error', 'No tienes una caja abierta. Debes abrir una caja primero.');
            }

            // Obtener formas de pago activas
            $empresaId = $this->empresaActivaId();
            $formasPago = \App\Models\FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            // Inicializar totales del sistema por forma de pago (ingresos)
            $totalesSistema = [];
            foreach ($formasPago as $forma) {
                $totalesSistema[$forma->clave] = 0;
            }

            // Calcular ingresos del sistema por forma de pago
            $ingresosSistema = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->where('tipo', 'ingreso')
                ->select('forma_pago', DB::raw('SUM(monto) as total'))
                ->groupBy('forma_pago')
                ->get();

            foreach ($ingresosSistema as $mov) {
                if (isset($totalesSistema[$mov->forma_pago])) {
                    $totalesSistema[$mov->forma_pago] = floatval($mov->total);
                }
            }

            // Calcular egresos del sistema por forma de pago
            $egresosSistema = [];
            foreach ($formasPago as $forma) {
                $egresosSistema[$forma->clave] = 0;
            }

            $egresosData = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->where('tipo', 'egreso')
                ->select('forma_pago', DB::raw('SUM(monto) as total'))
                ->groupBy('forma_pago')
                ->get();

            foreach ($egresosData as $mov) {
                if (isset($egresosSistema[$mov->forma_pago])) {
                    $egresosSistema[$mov->forma_pago] = floatval($mov->total);
                }
            }

            $totalIngresos = $apertura->total_ingresos;
            $totalEgresos = $apertura->total_egresos;
            $totalSistema = $apertura->monto_inicial + $totalIngresos - $totalEgresos;

            $arqueos = CajaArqueo::where('caja_apertura_id', $apertura->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('cajas.arqueos', compact('apertura', 'totalesSistema', 'egresosSistema', 'totalSistema', 'arqueos', 'formasPago', 'totalIngresos', 'totalEgresos'));
        } catch (\Exception $e) {
            Log::error('Error al cargar arqueos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los arqueos.');
        }
    }

    public function registrarArqueo(Request $request)
    {
        try {
            DB::beginTransaction();

            $apertura = CajaApertura::findOrFail($request->apertura_id);

            // Obtener formas de pago activas
            $empresaId = $this->empresaActivaId();
            $formasPago = \App\Models\FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->get();

            // Validar y construir array de montos contados
            $montosContado = [];
            $totalContado = 0;

            foreach ($formasPago as $forma) {
                $campo = $forma->clave . '_contado';
                $monto = floatval($request->input($campo, 0));
                $montosContado[$forma->clave] = $monto;
                $totalContado += $monto;
            }

            // Validar que efectivo sea > 0
            if (($montosContado['efectivo'] ?? 0) <= 0) {
                throw new \Exception('El campo Efectivo contado es obligatorio y debe ser mayor a 0.');
            }

            // Calcular total sistema
            $totalSistema = $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos;
            $diferencia = $totalContado - $totalSistema;

            // Registrar arqueo
            $arqueo = CajaArqueo::create([
                'caja_apertura_id' => $apertura->id,
                'user_id' => auth()->id(),
                'sucursal_id' => $apertura->sucursal_id,
                'fecha_arqueo' => now(),
                'efectivo_contado' => $montosContado['efectivo'] ?? 0,
                'tarjeta_debito_contado' => $montosContado['tarjeta_debito'] ?? 0,
                'tarjeta_credito_contado' => $montosContado['tarjeta_credito'] ?? 0,
                'vale_contado' => $montosContado['vale'] ?? 0,
                'transferencia_contado' => $montosContado['transferencia'] ?? 0,
                'cheque_contado' => $montosContado['cheque'] ?? 0,
                'total_contado' => $totalContado,
                'total_sistema' => $totalSistema,
                'diferencia' => $diferencia,
                'observaciones' => $request->observaciones,
                'estado' => $request->estado
            ]);

            // === SI HAY SOBRANTE, SE RETIRA DE LA CAJA ===
            if ($diferencia > 0.01) {
                // Registrar movimiento de retiro por el sobrante
                $movimiento = CajaMovimiento::create([
                    'caja_apertura_id' => $apertura->id,
                    'user_id' => auth()->id(),
                    'sucursal_id' => $apertura->sucursal_id,
                    'tipo' => 'egreso',
                    'categoria' => 'retiro_parcial',
                    'forma_pago' => 'efectivo',
                    'monto' => $diferencia,
                    'concepto' => "RETIRO POR SOBRANTE DE ARQUEO - Diferencia positiva detectada",
                    'referencia' => "ARQUEO-{$arqueo->id}",
                    'requiere_autorizacion' => false
                ]);

                // Actualizar totales de la apertura
                $apertura->increment('total_egresos', $diferencia);

                // ACTUALIZAR SALDO DE LA CAJA (se resta el sobrante)
                $apertura->caja->decrement('saldo_actual', $diferencia);

                $mensajeRetiro = " Se retiró el sobrante de $" . number_format($diferencia, 2) . " de la caja.";
            } else {
                $mensajeRetiro = "";
            }

            // Si hay faltante, solo se registra (NO se ajusta la caja)
            if ($diferencia < -0.01) {
                $mensajeRetiro = " Se detectó un faltante de $" . number_format(abs($diferencia), 2) . ". Verifica la diferencia.";
            }

            DB::commit();

            $mensaje = $arqueo->estado == 'finalizado'
                ? 'Arqueo finalizado correctamente.'
                : 'Arqueo guardado como borrador.';

            $mensaje .= $mensajeRetiro;

            return redirect()->route('cajas.arqueos')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar arqueo: ' . $e->getMessage());
            return back()->with('error', 'Error al registrar el arqueo: ' . $e->getMessage());
        }
    }

    public function verArqueo(CajaArqueo $arqueo)
    {
        try {
            $arqueo->load(['cajaApertura.caja', 'usuario']);
            return view('cajas.ver-arqueo', compact('arqueo'));
        } catch (\Exception $e) {
            Log::error('Error al ver arqueo: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el arqueo.');
        }
    }
    // Método para imprimir ticket de movimiento
    public function imprimirTicketMovimiento(CajaMovimiento $movimiento)
    {
        try {
            $movimiento->load(['cajaApertura.caja', 'usuario']);
            $ticketService = new TicketService();
            return $ticketService->movimientoTicket($movimiento);
        } catch (\Exception $e) {
            Log::error('Error al imprimir ticket movimiento: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el ticket: ' . $e->getMessage());
        }
    }

    // Método para imprimir ticket de transferencia
    public function imprimirTicketTransferencia(CajaTransferencia $transferencia)
    {
        try {
            $transferencia->load(['cajaOrigen', 'cajaDestino', 'usuario', 'autorizador']);
            $ticketService = new TicketService();
            return $ticketService->transferenciaTicket($transferencia);
        } catch (\Exception $e) {
            Log::error('Error al imprimir ticket transferencia: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el ticket: ' . $e->getMessage());
        }
    }

    // Método para imprimir ticket de arqueo
    public function imprimirTicketArqueo(CajaArqueo $arqueo)
    {
        try {
            $arqueo->load(['cajaApertura.caja', 'usuario']);
            $ticketService = new TicketService();
            return $ticketService->arqueoTicket($arqueo);
        } catch (\Exception $e) {
            Log::error('Error al imprimir ticket arqueo: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el ticket: ' . $e->getMessage());
        }
    }

    // Método para imprimir ticket de cierre
    public function imprimirTicketCierre($aperturaId)
    {
        try {
            $resumen = CajaService::resumenDia($aperturaId);
            $apertura = CajaApertura::with(['caja', 'usuario'])->findOrFail($aperturaId);
            $ticketService = new TicketService();
            return $ticketService->cierreTicket($apertura, $resumen);
        } catch (\Exception $e) {
            Log::error('Error al imprimir ticket cierre: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el ticket: ' . $e->getMessage());
        }
    }
    // Agregar después del método imprimirTicketArqueo

    public function imprimirArqueo(CajaArqueo $arqueo)
    {
        try {
            $arqueo->load(['cajaApertura.caja', 'usuario', 'sucursal']);

            // Obtener formas de pago activas
            $empresaId = $this->empresaActivaId();
            $formasPago = \App\Models\FormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();

            return view('cajas.imprimir-arqueo', compact('arqueo', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al imprimir arqueo: ' . $e->getMessage());
            return back()->with('error', 'Error al generar la impresión del arqueo.');
        }
    }
    /**
     * Registrar retiro parcial de caja
     */
    public function registrarRetiro(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'apertura_id' => 'required|exists:caja_aperturas,id',
                'forma_pago' => 'required|string',
                'monto' => 'required|numeric|min:0.01',
                'motivo' => 'required|string|max:500',
                'referencia' => 'nullable|string|max:100',
                'requiere_autorizacion' => 'boolean',
            ]);

            $apertura = CajaApertura::findOrFail($validated['apertura_id']);

            if ($apertura->estado !== 'abierta') {
                throw new \Exception('La caja no está abierta.');
            }

            // Verificar saldo suficiente
            $saldoActual = $apertura->saldoActual();
            if ($saldoActual < $validated['monto']) {
                throw new \Exception("Saldo insuficiente. Saldo actual: $" . number_format($saldoActual, 2));
            }

            // Registrar movimiento de retiro
            $movimiento = CajaMovimiento::create([
                'caja_apertura_id' => $apertura->id,
                'user_id' => auth()->id(),
                'sucursal_id' => $apertura->sucursal_id,
                'tipo' => 'egreso',
                'categoria' => 'retiro_parcial',
                'forma_pago' => $validated['forma_pago'],
                'monto' => $validated['monto'],
                'concepto' => "RETIRO PARCIAL - {$validated['motivo']}",
                'referencia' => $validated['referencia'] ?? null,
                'requiere_autorizacion' => $validated['requiere_autorizacion'] ?? false
            ]);

            // Actualizar totales de la apertura
            $apertura->increment('total_egresos', $validated['monto']);

            // Si no requiere autorización, actualizar saldo inmediatamente
            if (!$movimiento->requiere_autorizacion) {
                $apertura->caja->decrement('saldo_actual', $validated['monto']);
            }

            DB::commit();

            $mensaje = 'Retiro registrado correctamente.';
            if ($movimiento->requiere_autorizacion) {
                $mensaje = 'Retiro registrado. Requiere autorización de un administrador.';
            }

            return redirect()->route('cajas.operaciones')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar retiro: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
}