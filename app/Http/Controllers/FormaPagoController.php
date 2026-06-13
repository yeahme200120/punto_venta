<?php
// app/Http/Controllers/FormaPagoController.php

namespace App\Http\Controllers;

use App\Models\FormaPago;
use App\Models\Empresa;
use App\Models\EmpresaFormaPago;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FormaPagoController extends Controller
{
    use ActivaTrait;

    /**
     * Listado de formas de pago GLOBALES (para administración)
     */
    public function index()
    {
        try {
            $formasPago = FormaPago::orderBy('orden')
                ->paginate(15);

            return view('formas_pago.index', compact('formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al listar formas de pago: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las formas de pago.');
        }
    }

    /**
     * Configuración de formas de pago por empresa
     */
    public function configurarPorEmpresa($empresaId = null)
    {
        try {
            $empresaId = $empresaId ?? $this->empresaActivaId();
            $empresa = Empresa::findOrFail($empresaId);
            
            // Todas las formas de pago globales activas
            $todasFormas = FormaPago::where('activo_global', true)
                ->orderBy('orden')
                ->get();
            
            // Configuraciones actuales de la empresa (de la tabla pivote)
            $configuraciones = EmpresaFormaPago::where('empresa_id', $empresaId)
                ->pluck('activo', 'forma_pago_id')
                ->toArray();
            
            // Órdenes personalizados
            $ordenes = EmpresaFormaPago::where('empresa_id', $empresaId)
                ->pluck('orden_empresa', 'forma_pago_id')
                ->toArray();
            
            return view('formas_pago.configurar-empresa', compact('empresa', 'todasFormas', 'configuraciones', 'ordenes'));
        } catch (\Exception $e) {
            Log::error('Error al configurar formas de pago por empresa: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la configuración.');
        }
    }

    /**
     * Guardar configuración de formas de pago por empresa
     */
    public function actualizarConfiguracion(Request $request, $empresaId)
    {
        try {
            $empresa = Empresa::findOrFail($empresaId);
            $formasActivas = $request->input('formas_activas', []);
            
            // Obtener todas las formas de pago globales activas
            $todasFormas = FormaPago::where('activo_global', true)->get();
            
            foreach ($todasFormas as $forma) {
                $activo = in_array($forma->id, $formasActivas);
                $orden = $request->input("orden_{$forma->id}", $forma->orden);
                
                EmpresaFormaPago::updateOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'forma_pago_id' => $forma->id
                    ],
                    [
                        'activo' => $activo,
                        'orden_empresa' => $orden
                    ]
                );
            }
            
            return redirect()->route('formas_pago.configurar.empresa', $empresaId)
                ->with('success', 'Configuración actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar la configuración.');
        }
    }

    /**
     * Obtener formas de pago activas para la empresa actual
     */
    public function getActivas()
    {
        try {
            $empresaId = $this->empresaActivaId();
            
            // Obtener formas de pago activas para la empresa desde la tabla pivote
            $formasPago = EmpresaFormaPago::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->with('formaPago')
                ->orderBy('orden_empresa')
                ->get()
                ->pluck('formaPago');
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $formasPago
                ]);
            }
            
            return $formasPago;
        } catch (\Exception $e) {
            Log::error('Error al obtener formas de pago activas: ' . $e->getMessage());
            return request()->wantsJson() 
                ? response()->json(['success' => false, 'message' => 'Error'], 500)
                : collect();
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        try {
            return view('formas_pago.create');
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario create: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Guardar nueva forma de pago (GLOBAL)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'clave' => 'required|string|max:50|unique:forma_pagos,clave',
            'nombre' => 'required|string|max:100',
            'icono' => 'nullable|string|max:10',
            'orden' => 'nullable|integer|min:0',
            'activo_global' => 'boolean',
            'requiere_referencia' => 'boolean',
            'requiere_autorizacion' => 'boolean',
        ]);

        try {
            $formaPago = FormaPago::create([
                'clave' => $validated['clave'],
                'nombre' => $validated['nombre'],
                'icono' => $validated['icono'] ?? null,
                'orden' => $validated['orden'] ?? 0,
                'activo_global' => $request->has('activo_global'),
                'requiere_referencia' => $request->has('requiere_referencia'),
                'requiere_autorizacion' => $request->has('requiere_autorizacion'),
            ]);

            // 🔥 Si la nueva forma es globalmente activa, agregarla a todas las empresas existentes
            if ($formaPago->activo_global) {
                $empresas = Empresa::all();
                foreach ($empresas as $empresa) {
                    EmpresaFormaPago::updateOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'forma_pago_id' => $formaPago->id
                        ],
                        [
                            'activo' => true,
                            'orden_empresa' => $formaPago->orden
                        ]
                    );
                }
            }

            return redirect()->route('formas_pago.index')
                ->with('success', 'Forma de pago creada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear forma de pago: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al guardar la forma de pago.');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(FormaPago $formaPago)
    {
        try {
            return view('formas_pago.edit', compact('formaPago'));
        } catch (\Exception $e) {
            Log::error('Error al cargar edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Actualizar forma de pago (GLOBAL)
     */
    public function update(Request $request, FormaPago $formaPago)
    {
        $request->merge([
            'activo_global' => $request->has('activo_global'),
            'requiere_referencia' => $request->has('requiere_referencia'),
            'requiere_autorizacion' => $request->has('requiere_autorizacion'),
        ]);

        try {
            $validated = $request->validate([
                'clave' => 'required|string|max:50|unique:forma_pagos,clave,' . $formaPago->id,
                'nombre' => 'required|string|max:100',
                'icono' => 'nullable|string|max:10',
                'orden' => 'nullable|integer|min:0',
                'activo_global' => 'boolean',
                'requiere_referencia' => 'boolean',
                'requiere_autorizacion' => 'boolean',
            ]);

            $oldActivoGlobal = $formaPago->activo_global;
            $formaPago->update($validated);

            // 🔥 Si cambió el estado global, actualizar empresas
            if ($oldActivoGlobal != $formaPago->activo_global) {
                $empresas = Empresa::all();
                foreach ($empresas as $empresa) {
                    EmpresaFormaPago::updateOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'forma_pago_id' => $formaPago->id
                        ],
                        [
                            'activo' => $formaPago->activo_global,
                            'orden_empresa' => $formaPago->orden
                        ]
                    );
                }
            }

            return redirect()->route('formas_pago.index')
                ->with('success', 'Forma de pago actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar forma de pago: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la forma de pago.');
        }
    }

    public function destroy(FormaPago $formaPago)
    {
        // Verificar si tiene registros relacionados
        $hasRelations = EmpresaFormaPago::where('forma_pago_id', $formaPago->id)->exists();
            
        if ($hasRelations) {
            return back()->with('error', 'No se puede eliminar porque está configurada en una o más empresas.');
        }

        try {
            $formaPago->delete();
            return redirect()->route('formas_pago.index')
                ->with('success', 'Forma de pago eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar forma de pago: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la forma de pago.');
        }
    }

    /**
     * Activar/desactivar forma de pago (GLOBAL)
     */
    public function toggleActivo(FormaPago $formaPago)
    {
        try {
            $formaPago->activo_global = !$formaPago->activo_global;
            $formaPago->save();

            // 🔥 Sincronizar con todas las empresas
            $empresas = Empresa::all();
            foreach ($empresas as $empresa) {
                EmpresaFormaPago::updateOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'forma_pago_id' => $formaPago->id
                    ],
                    [
                        'activo' => $formaPago->activo_global,
                        'orden_empresa' => $formaPago->orden
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'activo' => $formaPago->activo_global,
                'message' => $formaPago->activo_global ? 'Forma de pago activada' : 'Forma de pago desactivada'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado'], 500);
        }
    }
}