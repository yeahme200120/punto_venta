<?php
// app/Http/Controllers/CajaController.php
namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaArqueo;
use App\Models\CajaMovimiento;
use App\Models\CajaTransferencia;
use App\Models\FormaPago;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\CajaService;
use App\Services\TicketService;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CajaController extends Controller
{
    use ActivaTrait;

    // ==================== CAJAS ====================

    public function indexCajas()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            if (!$empresaId) {
                return redirect()->route('dashboard')->with('error', 'No hay una empresa activa.');
            }

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
            return redirect()->route('cajas.cajas.index')
                ->with('success', 'Caja "' . $caja->nombre . '" actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar caja: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la caja.');
        }
    }

    public function aperturaIndex()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();
            $userId = auth()->id();
            $user = auth()->user();

            if (!$empresaId) {
                return redirect()->route('dashboard')->with('error', 'No hay una empresa activa.');
            }

            if (!$sucursalId) {
                return redirect()->route('dashboard')->with('error', 'No hay una sucursal activa.');
            }

            // 🔥 NUEVO: Obtener cajas abiertas de días anteriores (para mostrar advertencia)
            $cajasAnteriores = CajaApertura::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', 'abierta')
                ->whereDate('fecha', '<', today())
                ->when(!$user->hasRole('Super Admin') && !$user->hasRole('Administrador'), function ($q) use ($userId) {
                    return $q->where('user_id', $userId);
                })
                ->with(['caja', 'usuario'])
                ->orderBy('fecha', 'asc')
                ->get();

            // 🔥 NUEVO: Verificar si hay cajas anteriores y mostrar mensaje
            if ($cajasAnteriores->isNotEmpty()) {
                $fechas = $cajasAnteriores->pluck('fecha')->map(function ($f) {
                    return $f->format('d/m/Y');
                })->unique()->implode(', ');

                session()->flash('warning', "⚠️ Hay cajas abiertas de días anteriores ({$fechas}). Debes cerrarlas antes de abrir nuevas cajas.");
            }

            // 🔥 MODIFICADO: Filtrar solo cajas del DÍA ACTUAL
            if ($user->hasRole('Super Admin')) {
                $aperturasActivas = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', today())  // 🔥 SOLO DEL DÍA ACTUAL
                    ->with(['caja', 'usuario'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $tieneAperturaPropia = $aperturasActivas->contains('user_id', $userId);
                $aperturaAnterior = null;
            } else {
                // Usuario normal: solo su apertura del día actual
                $aperturasActivas = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('user_id', $userId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', today())  // 🔥 SOLO DEL DÍA ACTUAL
                    ->with(['caja', 'usuario'])
                    ->get();

                $tieneAperturaPropia = $aperturasActivas->isNotEmpty();

                // Buscar apertura anterior del mismo usuario (días anteriores)
                $aperturaAnterior = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('user_id', $userId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', '<', today())
                    ->first();
            }

            // 🔥 MODIFICADO: Cajas disponibles para apertura (excluyendo las que ya tienen apertura activa HOY)
            $cajasDisponibles = Caja::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('activo', true)
                ->where(function ($q) use ($user, $userId, $empresaId, $sucursalId) {
                    $esAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');

                    if ($esAdmin) {
                        // Admins: solo excluir si ya tienen ESA misma caja abierta HOY
                        $q->whereNotExists(function ($sub) use ($userId, $empresaId, $sucursalId) {
                            $sub->select(DB::raw(1))
                                ->from('caja_aperturas')
                                ->whereColumn('caja_aperturas.caja_id', 'cajas.id')
                                ->where('caja_aperturas.user_id', $userId)
                                ->where('caja_aperturas.empresa_id', $empresaId)
                                ->where('caja_aperturas.sucursal_id', $sucursalId)
                                ->where('caja_aperturas.estado', 'abierta')
                                ->whereDate('caja_aperturas.fecha', today());  // 🔥 SOLO HOY
                        });
                    } else {
                        // Usuarios normales: regla estándar
                        $q->where(function ($q2) {
                            $q2->where('permite_multiple', true)
                                ->orWhere(function ($q3) {
                                    $q3->where('permite_multiple', false)
                                        ->whereDoesntHave('aperturaActual', function ($q4) {
                                            $q4->where('estado', 'abierta')
                                                ->whereDate('fecha', today());  // 🔥 SOLO HOY
                                        });
                                });
                        });
                    }
                })
                ->get();

            return view('cajas.apertura', compact(
                'aperturasActivas',
                'cajasDisponibles',
                'tieneAperturaPropia',
                'aperturaAnterior',
                'cajasAnteriores'  // 🔥 Pasar a la vista
            ));
        } catch (\Exception $e) {
            Log::error('Error al cargar apertura: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la apertura de caja: ' . $e->getMessage());
        }
    }

    public function abrirCaja(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson();

        $validated = $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'monto_inicial' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $sucursalId = $this->sucursalActivaId();
        $empresaId = $this->empresaActivaId();
        $user = auth()->user();

        if (!$sucursalId) {
            $error = 'No hay una sucursal activa.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $error], 400)
                : back()->with('error', $error);
        }

        if (!$empresaId) {
            $error = 'No hay una empresa activa.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $error], 400)
                : back()->with('error', $error);
        }

        // 🔥 NUEVO: Verificar si hay cajas abiertas de días anteriores
        $cajasAnteriores = CajaApertura::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta')
            ->whereDate('fecha', '<', today())
            ->when(!$user->hasRole('Super Admin') && !$user->hasRole('Administrador'), function ($q) use ($user) {
                return $q->where('user_id', $user->id);
            })
            ->exists();

        if ($cajasAnteriores) {
            $error = '❌ Hay cajas abiertas de días anteriores. Debes cerrarlas antes de abrir una nueva caja.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $error], 400)
                : back()->with('error', $error);
        }

        // 🔥 NUEVO: Verificar caja abierta HOY - SOLO para usuarios normales (NO Admins)
        $esSuperAdmin = $user->hasRole('Super Admin');
        $esAdmin = $user->hasRole('Administrador');

        if (!$esSuperAdmin && !$esAdmin) {
            // Solo aplicar la restricción de "una caja por día" para usuarios normales
            $aperturaHoy = CajaApertura::where('user_id', auth()->id())
                ->where('sucursal_id', $sucursalId)
                ->where('empresa_id', $empresaId)
                ->where('estado', 'abierta')
                ->whereDate('fecha', today())
                ->exists();

            if ($aperturaHoy) {
                $error = '❌ Ya tienes una caja abierta para hoy. No puedes abrir otra.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error], 400)
                    : back()->with('error', $error);
            }
        } else {
            // Para Super Admin y Administrador: solo verificar que no estén abriendo la MISMA caja dos veces
            $mismaCajaAbierta = CajaApertura::where('user_id', auth()->id())
                ->where('caja_id', $validated['caja_id'])
                ->where('sucursal_id', $sucursalId)
                ->where('empresa_id', $empresaId)
                ->where('estado', 'abierta')
                ->whereDate('fecha', today())
                ->exists();

            if ($mismaCajaAbierta) {
                $error = '❌ Ya tienes esta caja abierta para hoy. No puedes abrir la misma caja dos veces.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error], 400)
                    : back()->with('error', $error);
            }

            // Verificar si la caja permite múltiples aperturas (solo si no es Admin)
            $caja = Caja::find($validated['caja_id']);
            if (!$caja->permite_multiple) {
                $cajaOcupada = CajaApertura::where('caja_id', $validated['caja_id'])
                    ->where('sucursal_id', $sucursalId)
                    ->where('empresa_id', $empresaId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', today())
                    ->where('user_id', '!=', auth()->id())
                    ->exists();

                if ($cajaOcupada) {
                    $error = '❌ Esta caja no permite múltiples aperturas y ya está siendo utilizada por otro usuario.';
                    return $isAjax
                        ? response()->json(['success' => false, 'message' => $error], 400)
                        : back()->with('error', $error);
                }
            }
        }

        try {
            $apertura = CajaService::abrirCaja(
                $validated['caja_id'],
                auth()->id(),
                $sucursalId,
                $empresaId,
                $validated['monto_inicial'],
                $validated['observaciones'] ?? null
            );

            $mensaje = '✅ Caja abierta correctamente para hoy.';

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'redirect' => route('cajas.operaciones')
                ]);
            }

            return redirect()->route('cajas.operaciones')->with('success', $mensaje);

        } catch (\Exception $e) {
            return $isAjax
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 500)
                : back()->with('error', $e->getMessage());
        }
    }

    // ==================== MOVIMIENTOS ====================

    public function operaciones()
    {
        try {
            Log::info('=== INICIO OPERACIONES ===');

            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();
            $userId = auth()->id();
            $user = auth()->user();

            Log::info('Datos básicos:', [
                'user_id' => $userId,
                'user_email' => $user->email,
                'user_roles' => $user->getRoleNames()->implode(', '),
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId
            ]);

            if (!$empresaId) {
                Log::warning('No hay empresa activa');
                return redirect()->route('dashboard')->with('error', 'No hay una empresa activa.');
            }

            if (!$sucursalId) {
                Log::warning('No hay sucursal activa');
                return redirect()->route('dashboard')->with('error', 'No hay una sucursal activa.');
            }

            // 🔥 VARIABLE PARA ALMACENAR LA APERTURA SELECCIONADA
            $apertura = null;
            $todasAperturas = collect();

            // Para Super Admin y Administrador: pueden ver todas las cajas abiertas de la sucursal
            if ($user->hasRole('Super Admin') || $user->hasRole('Administrador')) {
                Log::info('Usuario es Super Admin o Administrador, buscando TODAS las cajas abiertas');

                $todasAperturas = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')
                    ->with(['caja', 'usuario'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                Log::info('Cajas abiertas encontradas:', [
                    'total' => $todasAperturas->count(),
                    'ids' => $todasAperturas->pluck('id')->toArray(),
                    'usuarios' => $todasAperturas->map(function ($a) {
                        return $a->usuario->name . ' (' . $a->usuario->email . ')';
                    })->toArray(),
                    'cajas' => $todasAperturas->map(function ($a) {
                        return $a->caja->nombre . ' (' . $a->caja->codigo . ')';
                    })->toArray()
                ]);

                if ($todasAperturas->isEmpty()) {
                    Log::warning('No hay cajas abiertas en esta sucursal');
                    return redirect()->route('cajas.apertura')
                        ->with('error', 'No hay cajas abiertas en esta sucursal.');
                }

                // Obtener apertura seleccionada de sesión o usar la primera
                $aperturaIdSession = session('caja_operacion_id');
                Log::info('Apertura en sesión:', ['session_apertura_id' => $aperturaIdSession]);

                if ($aperturaIdSession) {
                    $apertura = $todasAperturas->firstWhere('id', $aperturaIdSession);
                    Log::info('Apertura encontrada por sesión:', ['encontrada' => $apertura ? 'SÍ' : 'NO']);
                }

                if (!$apertura) {
                    $apertura = $todasAperturas->first();
                    session(['caja_operacion_id' => $apertura->id]);
                    Log::info('Seleccionando primera apertura:', ['apertura_id' => $apertura->id]);
                }
            } else {
                Log::info('Usuario normal (no Super Admin/Admin), buscando SOLO su propia caja');

                $apertura = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('user_id', $userId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', today())
                    ->first();

                Log::info('Caja del día actual encontrada:', [
                    'encontrada' => $apertura ? 'SÍ' : 'NO',
                    'id' => $apertura->id ?? 'N/A',
                    'caja' => $apertura->caja->nombre ?? 'N/A',
                    'fecha' => $apertura ? $apertura->fecha->format('d/m/Y') : 'N/A'
                ]);

                if (!$apertura) {
                    // Buscar si tiene alguna caja abierta de días anteriores
                    $aperturaAnterior = CajaApertura::where('empresa_id', $empresaId)
                        ->where('sucursal_id', $sucursalId)
                        ->where('user_id', $userId)
                        ->where('estado', 'abierta')
                        ->first();

                    if ($aperturaAnterior) {
                        Log::warning('Usuario tiene caja abierta de día anterior:', [
                            'fecha' => $aperturaAnterior->fecha->format('d/m/Y'),
                            'caja_id' => $aperturaAnterior->id
                        ]);
                        return redirect()->route('cajas.apertura')
                            ->with('error', 'Tienes una caja abierta del día ' . $aperturaAnterior->fecha->format('d/m/Y') . '. Debes cerrarla antes de operar.');
                    }

                    Log::warning('Usuario no tiene ninguna caja abierta');
                    return redirect()->route('cajas.apertura')
                        ->with('error', 'No tienes una caja abierta para hoy. Debes abrir una caja primero.');
                }

                $todasAperturas = collect([$apertura]);
            }

            // Verificar que $apertura no sea null
            if (!$apertura) {
                Log::error('CRÍTICO: $apertura es null después de toda la lógica');
                return redirect()->route('cajas.apertura')
                    ->with('error', 'No se pudo identificar una caja activa.');
            }

            Log::info('Apertura seleccionada final:', [
                'apertura_id' => $apertura->id,
                'caja_nombre' => $apertura->caja->nombre,
                'caja_codigo' => $apertura->caja->codigo,
                'usuario' => $apertura->usuario->name,
                'fecha' => $apertura->fecha->format('d/m/Y'),
                'saldo_actual' => $apertura->saldoActual()
            ]);

            // Obtener movimientos de la apertura seleccionada
            $movimientos = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            Log::info('Movimientos encontrados:', [
                'total' => $movimientos->total(),
                'count' => $movimientos->count()
            ]);

            $resumen = CajaService::resumenDia($apertura->id);
            Log::info('Resumen del día:', [
                'apertura' => $resumen['apertura'],
                'total_ingresos' => $resumen['total_ingresos'],
                'total_egresos' => $resumen['total_egresos'],
                'saldo_esperado' => $resumen['saldo_esperado']
            ]);

            $formasPago = FormaPago::getActivasPorEmpresa($empresaId);

            Log::info('=== FIN OPERACIONES - OK ===');

            return view('cajas.operaciones', compact('apertura', 'movimientos', 'resumen', 'formasPago', 'todasAperturas'));

        } catch (\Exception $e) {
            Log::error('Error al cargar operaciones: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error al cargar las operaciones: ' . $e->getMessage());
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
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $movimientosPendientes = CajaMovimiento::where('requiere_autorizacion', true)
                ->whereNull('autorizado_por')
                ->whereHas('cajaApertura', function ($q) use ($empresaId, $sucursalId) {
                    $q->where('empresa_id', $empresaId);
                    if ($sucursalId) {
                        $q->where('sucursal_id', $sucursalId);
                    }
                })
                ->with(['cajaApertura', 'usuario'])
                ->orderBy('created_at')
                ->paginate(20);

            $transferenciasPendientes = CajaTransferencia::where('estado', 'pendiente')
                ->whereHas('cajaOrigen', function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                })
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
            // 🔥 Ya no pasamos el ID del usuario, solo la contraseña
            $movimiento = CajaService::autorizarMovimiento(
                $movimientoId,
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
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            $cajas = Caja::where('empresa_id', $empresaId)
                ->when($sucursalId, function ($q) use ($sucursalId) {
                    return $q->where('sucursal_id', $sucursalId);
                })
                ->where('activo', true)
                ->get();
            // 🔥 Agregar formas de pago activas
            $formasPago = FormaPago::getActivasPorEmpresa($empresaId);

            return view('cajas.transferencias', compact('cajas', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al cargar transferencias: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las transferencias.');
        }
    }

    public function solicitarTransferencia(Request $request)
    {
        // 🔥 Obtener formas de pago activas globalmente desde la base de datos
        $formasPagoValidas = FormaPago::where('activo_global', true)->pluck('clave')->toArray();

        // Si no hay formas de pago en BD, usar valores por defecto
        if (empty($formasPagoValidas)) {
            $formasPagoValidas = ['efectivo', 'tarjeta_debito', 'tarjeta_credito', 'vale', 'transferencia', 'cheque'];
        }

        $validated = $request->validate([
            'caja_origen_id' => 'required|exists:cajas,id',
            'caja_destino_id' => 'required|exists:cajas,id|different:caja_origen_id',
            'monto' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:500',
            'forma_pago' => 'required|string|in:' . implode(',', $formasPagoValidas),
        ]);

        try {
            $transferencia = CajaService::transferirEntreCajas(
                $validated['caja_origen_id'],
                $validated['caja_destino_id'],
                auth()->id(),
                $validated['monto'],
                $validated['motivo'],
                $validated['forma_pago']
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
            // 🔥 Ya no pasamos el ID del usuario, solo la contraseña
            $transferencia = CajaService::aprobarTransferencia(
                $transferenciaId,
                $validated['password_maestra']
            );

            return redirect()->route('cajas.autorizaciones')
                ->with('success', 'Transferencia autorizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reporteDia($aperturaId)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $user = auth()->user();

            $resumen = CajaService::resumenDia($aperturaId);
            $apertura = CajaApertura::with(['caja', 'usuario', 'movimientos', 'sucursal'])->findOrFail($aperturaId);

            if ($apertura->empresa_id != $empresaId) {
                return back()->with('error', 'No tienes permiso para ver este reporte.');
            }

            // 🔥 Obtener todas las cajas abiertas para el selector (solo para Admin/Super Admin)
            $todasAperturas = collect();
            if ($user->hasRole('Super Admin') || $user->hasRole('Administrador')) {
                $todasAperturas = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $this->sucursalActivaId())
                    ->where('estado', 'abierta')
                    ->with(['caja', 'usuario'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            $formasPago = FormaPago::getActivasPorEmpresa($empresaId);

            // 🔥 Movimientos pendientes de autorización
            $movimientosPendientes = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->where('requiere_autorizacion', true)
                ->whereNull('autorizado_por')
                ->get();

            return view('cajas.reporte-dia', compact('resumen', 'apertura', 'formasPago', 'todasAperturas', 'movimientosPendientes'));
        } catch (\Exception $e) {
            Log::error('Error al generar reporte: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }

    // ==================== ARQUEOS ====================

    public function arqueos()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();
            $userId = auth()->id();
            $user = auth()->user();

            if ($user->hasRole('Super Admin')) {
                $apertura = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')
                    ->first();
            } else {
                $apertura = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('user_id', $userId)
                    ->where('estado', 'abierta')
                    ->first();
            }

            if (!$apertura) {
                return redirect()->route('cajas.apertura')
                    ->with('error', 'No hay una caja abierta en esta sucursal.');
            }

            $formasPago = FormaPago::getActivasPorEmpresa($empresaId);

            // Inicializar totales del sistema por forma de pago (ingresos)
            $totalesSistema = [];
            foreach ($formasPago as $forma) {
                $totalesSistema[$forma->clave] = 0;
            }

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
            $empresaId = $this->empresaActivaId();

            // Verificar que la apertura pertenezca a la empresa activa
            if ($apertura->empresa_id != $empresaId) {
                throw new \Exception('No tienes permiso para registrar arqueo en esta apertura.');
            }

            $formasPago = FormaPago::getActivasPorEmpresa($empresaId);

            $montosContado = [];
            $totalContado = 0;

            foreach ($formasPago as $forma) {
                $campo = $forma->clave . '_contado';
                $monto = floatval($request->input($campo, 0));
                $montosContado[$forma->clave] = $monto;
                $totalContado += $monto;
            }

            if (($montosContado['efectivo'] ?? 0) <= 0) {
                throw new \Exception('El campo Efectivo contado es obligatorio y debe ser mayor a 0.');
            }

            $totalSistema = $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos;
            $diferencia = $totalContado - $totalSistema;

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

            $mensajeRetiro = "";

            if ($diferencia > 0.01) {
                CajaMovimiento::create([
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

                $apertura->increment('total_egresos', $diferencia);
                $apertura->caja->decrement('saldo_actual', $diferencia);
                $mensajeRetiro = " Se retiró el sobrante de $" . number_format($diferencia, 2) . " de la caja.";
            }

            if ($diferencia < -0.01) {
                $mensajeRetiro = " Se detectó un faltante de $" . number_format(abs($diferencia), 2) . ". Verifica la diferencia.";
            }

            DB::commit();

            $mensaje = $arqueo->estado == 'finalizado'
                ? 'Arqueo finalizado correctamente.'
                : 'Arqueo guardado como borrador.';
            $mensaje .= $mensajeRetiro;

            return redirect()->route('cajas.arqueos')->with('success', $mensaje);

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

    // ==================== TICKETS ====================

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

    public function imprimirArqueo(CajaArqueo $arqueo)
    {
        try {
            $arqueo->load(['cajaApertura.caja', 'usuario', 'sucursal']);
            $empresaId = $this->empresaActivaId();
            $formasPago = FormaPago::getActivasPorEmpresa($empresaId);

            return view('cajas.imprimir-arqueo', compact('arqueo', 'formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al imprimir arqueo: ' . $e->getMessage());
            return back()->with('error', 'Error al generar la impresión del arqueo.');
        }
    }

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
            $empresaId = $this->empresaActivaId();

            if ($apertura->empresa_id != $empresaId) {
                throw new \Exception('No tienes permiso para registrar retiros en esta apertura.');
            }

            if ($apertura->estado !== 'abierta') {
                throw new \Exception('La caja no está abierta.');
            }

            $requiereAutorizacion = $validated['requiere_autorizacion'] ?? false;

            // 🔥 Si requiere autorización, NO validar saldo (se validará al autorizar)
            if (!$requiereAutorizacion) {
                $saldoActual = $apertura->saldoActual();
                if ($saldoActual < $validated['monto']) {
                    throw new \Exception("Saldo insuficiente. Saldo actual: $" . number_format($saldoActual, 2));
                }
            }

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

            // 🔥 Solo actualizar totales si NO requiere autorización
            if (!$requiereAutorizacion) {
                $apertura->increment('total_egresos', $validated['monto']);
                $apertura->caja->decrement('saldo_actual', $validated['monto']);
            }

            DB::commit();

            $mensaje = 'Retiro registrado correctamente.';
            if ($movimiento->requiere_autorizacion) {
                $mensaje = 'Retiro registrado. Requiere autorización de un administrador.';
            }

            return redirect()->route('cajas.operaciones')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar retiro: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
    // app/Models/CajaApertura.php
    public static function getAperturaActual($empresaId, $sucursalId, $userId = null)
    {
        $query = self::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta');

        // Si no es Super Admin, filtrar por usuario
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }
    /**
     * Ver detalles de una apertura específica (solo lectura)
     */
    public function verApertura(CajaApertura $apertura)
    {
        try {
            $empresaId = $this->empresaActivaId();

            // Verificar que la apertura pertenezca a la empresa activa
            if ($apertura->empresa_id != $empresaId) {
                return redirect()->route('cajas.apertura')
                    ->with('error', 'No tienes permiso para ver esta apertura.');
            }

            $apertura->load([
                'caja',
                'usuario',
                'movimientos' => function ($q) {
                    $q->orderBy('created_at', 'desc')->limit(50);
                },
                'sucursal'
            ]);

            return view('cajas.ver-apertura', compact('apertura'));
        } catch (\Exception $e) {
            Log::error('Error al ver apertura: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la apertura.');
        }
    }

    public function cerrarCaja(Request $request)
    {
        // Detectar si es petición AJAX
        $isAjax = $request->ajax() || $request->wantsJson();

        $validated = $request->validate([
            'apertura_id' => 'required|exists:caja_aperturas,id',
            'monto_final' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
            'password_maestra' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $empresaId = $this->empresaActivaId();
            $sucursalId = $this->sucursalActivaId();

            if (!$empresaId) {
                throw new \Exception('No hay una empresa activa. Por favor, selecciona una empresa.');
            }

            if (!$sucursalId) {
                throw new \Exception('No hay una sucursal activa. Por favor, selecciona una sucursal.');
            }

            $apertura = CajaApertura::where('id', $validated['apertura_id'])
                ->where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->firstOrFail();

            if ($apertura->estado !== 'abierta') {
                throw new \Exception('Esta caja ya está cerrada o fue suspendida.');
            }

            // 🔥 CORREGIDO: Verificar permisos para cerrar caja
            $esSuperAdmin = $user->hasRole('Super Admin');
            $esAdmin = $user->hasRole('Administrador');
            $esPropietario = $apertura->user_id == $user->id;
            $puedeCerrar = false;

            // Super Admin puede cerrar cualquier caja
            if ($esSuperAdmin) {
                $puedeCerrar = true;
            }
            // Administrador puede cerrar cualquier caja de su empresa
            elseif ($esAdmin) {
                $puedeCerrar = true;
            }
            // Usuario normal solo puede cerrar sus propias cajas
            elseif ($esPropietario) {
                $puedeCerrar = true;
            }

            if (!$puedeCerrar) {
                throw new \Exception('No tienes permiso para cerrar esta caja. Solo el propietario, Administrador o Super Admin pueden cerrarla.');
            }

            // 🔥 CORREGIDO: Validar contraseña maestra para cerrar caja de otro usuario
            $esCajaAjena = !$esPropietario;
            if ($esCajaAjena) {
                // Si no es su caja, exige contraseña maestra (incluso si es admin, debe poner su contraseña)
                if (empty($validated['password_maestra'])) {
                    throw new \Exception('Debes ingresar la contraseña maestra para cerrar una caja de otro usuario.');
                }

                // Validar la contraseña de forma global (no atada al usuario logueado)
                $adminAutorizado = \App\Models\ContrasenaMaestra::validarPasswordGlobal(
                    $validated['password_maestra'],
                    $apertura->empresa_id
                );

                if (!$adminAutorizado) {
                    throw new \Exception('Contraseña maestra incorrecta o no tienes autorización para cerrar esta caja.');
                }

                // Opcional: registrar quién autorizó el cierre (puedes guardar $adminAutorizado->id en observaciones)
                $observacionesFinal = ($validated['observaciones'] ?? '') . " [Autorizado por: {$adminAutorizado->name} ({$adminAutorizado->email})]";
            }

            // Verificar retiros pendientes
            $retirosPendientes = CajaMovimiento::where('caja_apertura_id', $apertura->id)
                ->where('tipo', 'egreso')
                ->where('categoria', 'retiro_parcial')
                ->where('requiere_autorizacion', true)
                ->whereNull('autorizado_por')
                ->sum('monto');

            if ($retirosPendientes > 0) {
                throw new \Exception("Hay retiros pendientes de autorización por $" . number_format($retirosPendientes, 2) . ". Debes autorizarlos antes de cerrar la caja.");
            }

            // Calcular y registrar retiro final si es necesario
            $saldoEsperado = $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos;

            if ($validated['monto_final'] < $saldoEsperado) {
                $diferencia = $saldoEsperado - $validated['monto_final'];

                CajaMovimiento::create([
                    'caja_apertura_id' => $apertura->id,
                    'user_id' => $user->id,
                    'sucursal_id' => $apertura->sucursal_id,
                    'tipo' => 'egreso',
                    'categoria' => 'retiro_final',
                    'forma_pago' => 'efectivo',
                    'monto' => $diferencia,
                    'concepto' => "RETIRO FINAL DE CIERRE DE CAJA",
                    'referencia' => "CIERRE-{$apertura->id}",
                    'requiere_autorizacion' => false
                ]);

                $apertura->increment('total_egresos', $diferencia);
            }

            // Construir observaciones finales
            $observacionesFinal = $validated['observaciones'] ?? '';
            if ($esCajaAjena && ($esSuperAdmin || $esAdmin)) {
                $observacionesFinal = "[CIERRE POR ADMIN: {$user->name}] " . $observacionesFinal;
            }

            // Cerrar la caja
            CajaService::cerrarCaja(
                $validated['apertura_id'],
                $validated['monto_final'],
                $observacionesFinal
            );

            DB::commit();

            $mensaje = '✅ Caja cerrada correctamente.';
            if ($esCajaAjena && ($esSuperAdmin || $esAdmin)) {
                $usuarioNombre = $apertura->usuario->name ?? 'otro usuario';
                $mensaje = "✅ Caja cerrada correctamente por " . ($esSuperAdmin ? 'Super Administrador' : 'Administrador') . ". La caja pertenecía a: {$usuarioNombre}";
            }

            // Limpiar la sesión de notificación si era la última caja anterior
            if ($apertura->fecha->lt(today())) {
                $cajasAnterioresRestantes = CajaApertura::where('empresa_id', $empresaId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('estado', 'abierta')
                    ->whereDate('fecha', '<', today())
                    ->count();

                if ($cajasAnterioresRestantes === 0) {
                    session()->forget('cajas_anteriores_notificadas');
                }
            }

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'icon' => 'success',
                    'title' => '¡Caja cerrada!',
                    'message' => $mensaje,
                    'redirect' => route('cajas.apertura')
                ]);
            }

            return redirect()->route('cajas.apertura')->with('success', $mensaje);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $errors = $e->errors();
            $firstError = reset($errors)[0] ?? 'Error de validación';

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'title' => 'Error de validación',
                    'message' => $firstError
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            $error = '❌ No se encontró la apertura de caja o no pertenece a tu empresa/sucursal activa.';

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'title' => 'No encontrado',
                    'message' => $error
                ], 404);
            }
            return back()->with('error', $error);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cerrar caja: ' . $e->getMessage());

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'title' => 'Error al cerrar caja',
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }
    public function cambiarCajaOperacion(Request $request)
    {
        $request->validate([
            'apertura_id' => 'required|exists:caja_aperturas,id'
        ]);

        session(['caja_operacion_id' => $request->apertura_id]);

         // 🔥 Obtener la URL de origen para redirigir de vuelta
        $redirectUrl = $request->input('redirect', route('cajas.operaciones'));

        return redirect($redirectUrl)->with('success', 'Caja cambiada correctamente.');
    }
}