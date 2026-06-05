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
            return session('sucursal_activa_id');
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

            return view('cajas.create', compact('sucursales'));
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
            return redirect()->route('cajas.index')
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
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'permite_multiple' => 'boolean',
            'activo' => 'boolean',
        ]);

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
            return redirect()->route('cajas.index')
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
            CajaService::cerrarCaja(
                $validated['apertura_id'],
                $validated['monto_final'],
                $validated['observaciones'] ?? null
            );

            return redirect()->route('cajas.apertura')
                ->with('success', 'Caja cerrada correctamente.');
        } catch (\Exception $e) {
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

            return view('cajas.operaciones', compact('apertura', 'movimientos', 'resumen'));
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
            $resumen = CajaService::resumenDia($aperturaId);
            $apertura = CajaApertura::with(['caja', 'usuario', 'movimientos'])->findOrFail($aperturaId);

            return view('cajas.reporte-dia', compact('resumen', 'apertura'));
        } catch (\Exception $e) {
            Log::error('Error al generar reporte: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el reporte.');
        }
    }
    // En app/Http/Controllers/CajaController.php agregar:

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

            // Obtener movimientos del sistema agrupados por forma de pago
            $movimientosSistema = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->select(
                    'forma_pago',
                    DB::raw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE 0 END) as total_ingresos'),
                    DB::raw('SUM(CASE WHEN tipo = "egreso" THEN monto ELSE 0 END) as total_egresos')
                )
                ->groupBy('forma_pago')
                ->get();

            $totalesSistema = [
                'efectivo' => 0,
                'tarjeta_debito' => 0,
                'tarjeta_credito' => 0,
                'vale' => 0,
                'transferencia' => 0,
                'cheque' => 0,
            ];

            foreach ($movimientosSistema as $mov) {
                $neto = $mov->total_ingresos - $mov->total_egresos;
                $totalesSistema[$mov->forma_pago] = $neto;
            }

            $totalSistema = $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos;

            $arqueos = CajaArqueo::where('caja_apertura_id', $apertura->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('cajas.arqueos', compact('apertura', 'totalesSistema', 'totalSistema', 'arqueos'));
        } catch (\Exception $e) {
            Log::error('Error al cargar arqueos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los arqueos.');
        }
    }

    public function registrarArqueo(Request $request)
    {
        $validated = $request->validate([
            'efectivo_contado' => 'required|numeric|min:0',
            'tarjeta_debito_contado' => 'required|numeric|min:0',
            'tarjeta_credito_contado' => 'required|numeric|min:0',
            'vale_contado' => 'required|numeric|min:0',
            'transferencia_contado' => 'required|numeric|min:0',
            'cheque_contado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:borrador,finalizado'
        ]);

        DB::beginTransaction();
        try {
            $apertura = CajaApertura::findOrFail($request->apertura_id);

            // Calcular total contado
            $totalContado =
                $validated['efectivo_contado'] +
                $validated['tarjeta_debito_contado'] +
                $validated['tarjeta_credito_contado'] +
                $validated['vale_contado'] +
                $validated['transferencia_contado'] +
                $validated['cheque_contado'];

            // Calcular total sistema
            $totalSistema = $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos;

            $arqueo = CajaArqueo::create([
                'caja_apertura_id' => $apertura->id,
                'user_id' => auth()->id(),
                'sucursal_id' => $apertura->sucursal_id,
                'fecha_arqueo' => now(),
                'efectivo_contado' => $validated['efectivo_contado'],
                'tarjeta_debito_contado' => $validated['tarjeta_debito_contado'],
                'tarjeta_credito_contado' => $validated['tarjeta_credito_contado'],
                'vale_contado' => $validated['vale_contado'],
                'transferencia_contado' => $validated['transferencia_contado'],
                'cheque_contado' => $validated['cheque_contado'],
                'total_contado' => $totalContado,
                'total_sistema' => $totalSistema,
                'diferencia' => $totalContado - $totalSistema,
                'observaciones' => $validated['observaciones'],
                'estado' => $validated['estado']
            ]);

            DB::commit();

            $mensaje = $arqueo->estado == 'finalizado'
                ? 'Arqueo finalizado correctamente.'
                : 'Arqueo guardado como borrador.';

            return redirect()->route('cajas.arqueos')
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar arqueo: ' . $e->getMessage());
            return back()->with('error', 'Error al registrar el arqueo.');
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

    public function imprimirArqueo(CajaArqueo $arqueo)
    {
        try {
            $arqueo->load(['cajaApertura.caja', 'usuario']);
            return view('cajas.imprimir-arqueo', compact('arqueo'));
        } catch (\Exception $e) {
            Log::error('Error al imprimir arqueo: ' . $e->getMessage());
            return back()->with('error', 'Error al imprimir el arqueo.');
        }
    }
}