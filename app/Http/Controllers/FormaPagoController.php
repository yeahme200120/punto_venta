<?php
// app/Http/Controllers/FormaPagoController.php

namespace App\Http\Controllers;

use App\Models\FormaPago;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FormaPagoController extends Controller
{
    use ActivaTrait;

    /**
     * Listado de formas de pago de la empresa activa
     */
    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            if (!$empresaId) {
                return redirect()->route('dashboard')->with('error', 'No hay una empresa activa.');
            }

            $formasPago = FormaPago::where('empresa_id', $empresaId)
                ->orderBy('orden')
                ->paginate(15);

            return view('formas_pago.index', compact('formasPago'));
        } catch (\Exception $e) {
            Log::error('Error al listar formas de pago: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las formas de pago.');
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
     * Guardar nueva forma de pago
     */
    public function store(Request $request)
    {
        $empresaId = $this->empresaActivaId();
        if (!$empresaId) {
            return back()->with('error', 'No hay una empresa activa.');
        }

        $validated = $request->validate([
            'clave' => 'required|string|max:50|unique:forma_pagos,clave,NULL,id,empresa_id,' . $empresaId,
            'nombre' => 'required|string|max:100',
            'icono' => 'nullable|string|max:10',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'boolean',
            'requiere_referencia' => 'boolean',
            'requiere_autorizacion' => 'boolean',
        ]);

        try {
            FormaPago::create([
                'empresa_id' => $empresaId,
                'clave' => $validated['clave'],
                'nombre' => $validated['nombre'],
                'icono' => $validated['icono'] ?? null,
                'orden' => $validated['orden'] ?? 0,
                'activo' => $request->has('activo'),
                'requiere_referencia' => $request->has('requiere_referencia'),
                'requiere_autorizacion' => $request->has('requiere_autorizacion'),
            ]);

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
            $empresaId = $this->empresaActivaId();
            if ($formaPago->empresa_id != $empresaId) {
                return redirect()->route('formas_pago.index')
                    ->with('error', 'No tienes permiso para editar esta forma de pago.');
            }
            return view('formas_pago.edit', compact('formaPago'));
        } catch (\Exception $e) {
            Log::error('Error al cargar edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    public function update(Request $request, FormaPago $formaPago)
    {
        // Convertir checkboxes a booleanos antes de validar
        $request->merge([
            'activo' => $request->has('activo'),
            'requiere_referencia' => $request->has('requiere_referencia'),
            'requiere_autorizacion' => $request->has('requiere_autorizacion'),
        ]);

        Log::info('Update iniciado', ['formaPago_id' => $formaPago->id, 'user_id' => auth()->id()]);

        $empresaId = $this->empresaActivaId();
        Log::info('Empresa activa', ['empresaId' => $empresaId]);

        if ($formaPago->empresa_id != $empresaId) {
            Log::warning('Permiso denegado por empresa', ['forma_empresa' => $formaPago->empresa_id, 'session_empresa' => $empresaId]);
            return redirect()->route('formas_pago.index')->with('error', 'No tienes permiso para editar esta forma de pago.');
        }

        Log::info('Validación iniciada', ['request_all' => $request->all()]);

        try {
            $validated = $request->validate([
                'clave' => 'required|string|max:50|unique:forma_pagos,clave,' . $formaPago->id . ',id,empresa_id,' . $empresaId,
                'nombre' => 'required|string|max:100',
                'icono' => 'nullable|string|max:10',
                'orden' => 'nullable|integer|min:0',
                'activo' => 'boolean',
                'requiere_referencia' => 'boolean',
                'requiere_autorizacion' => 'boolean',
            ]);
            Log::info('Validación aprobada', $validated);

            $formaPago->update([
                'clave' => $validated['clave'],
                'nombre' => $validated['nombre'],
                'icono' => $validated['icono'] ?? null,
                'orden' => $validated['orden'] ?? 0,
                'activo' => $validated['activo'],
                'requiere_referencia' => $validated['requiere_referencia'],
                'requiere_autorizacion' => $validated['requiere_autorizacion'],
            ]);
            Log::info('Update ejecutado correctamente');

            return redirect()->route('formas_pago.index')->with('success', 'Forma de pago actualizada correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Excepción en update', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Error al actualizar la forma de pago.');
        }
    }

    public function destroy(FormaPago $formaPago)
    {
        $empresaId = $this->empresaActivaId();
        if ($formaPago->empresa_id != $empresaId) {
            return redirect()->route('formas_pago.index')
                ->with('error', 'No tienes permiso para eliminar esta forma de pago.');
        }

        // Verificar si tiene registros relacionados en pago_detalles
        if ($formaPago->pagoDetalles()->exists()) {
            return back()->with('error', 'No se puede eliminar porque tiene movimientos asociados.');
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
     * Activar/desactivar forma de pago
     */
    public function toggleActivo(FormaPago $formaPago)
    {
        $empresaId = $this->empresaActivaId();
        if ($formaPago->empresa_id != $empresaId) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $formaPago->activo = !$formaPago->activo;
            $formaPago->save();

            return response()->json([
                'success' => true,
                'activo' => $formaPago->activo,
                'message' => $formaPago->activo ? 'Forma de pago activada' : 'Forma de pago desactivada'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado'], 500);
        }
    }
}