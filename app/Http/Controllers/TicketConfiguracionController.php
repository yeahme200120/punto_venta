<?php
// app/Http/Controllers/TicketConfiguracionController.php

namespace App\Http\Controllers;

use App\Models\TicketConfiguracion;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TicketConfiguracionController extends Controller
{
    use ActivaTrait;

    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            if (!$empresaId) {
                return redirect()->route('dashboard')->with('error', 'No hay una empresa activa.');
            }

            $configuraciones = TicketConfiguracion::where('empresa_id', $empresaId)
                ->orderBy('tipo')
                ->paginate(10);

            return view('tickets.index', compact('configuraciones'));
        } catch (\Exception $e) {
            Log::error('Error al listar configuraciones de ticket: ' . $e->getMessage());
            return view('tickets.index', ['configuraciones' => collect([])])->with('error', 'Error al cargar las configuraciones.');
        }
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson();
        $empresaId = $this->empresaActivaId();

        if (!$empresaId) {
            $error = 'No hay una empresa activa.';
            if ($isAjax) {
                return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => $error], 400);
            }
            return back()->with('error', $error);
        }

        $messages = [
            'tipo.required' => 'El tipo de ticket es obligatorio.',
            'tipo.in' => 'El tipo de ticket debe ser: movimiento, transferencia, arqueo o cierre.',
            'logo.image' => 'El logo debe ser una imagen válida (jpg, jpeg, png, gif, webp).',
            'logo.max' => 'El logo no puede pesar más de 20MB.',
            'nombre_empresa.max' => 'El nombre de la empresa no puede exceder 255 caracteres.',
            'direccion.max' => 'La dirección no puede exceder 500 caracteres.',
            'telefono.max' => 'El teléfono no puede exceder 50 caracteres.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'email.max' => 'El correo no puede exceder 100 caracteres.',
            'rfc.max' => 'El RFC no puede exceder 20 caracteres.',
            'cabecera.max' => 'La cabecera no puede exceder 255 caracteres.',
            'footer.max' => 'El pie de página no puede exceder 255 caracteres.',
            'ancho_papel.in' => 'El ancho del papel debe ser 58mm o 80mm.',
            'fuente.in' => 'La fuente debe ser monospace, sans-serif o serif.',
            'tamano_fuente.integer' => 'El tamaño de fuente debe ser un número entero.',
            'tamano_fuente.min' => 'El tamaño de fuente mínimo es 8px.',
            'tamano_fuente.max' => 'El tamaño de fuente máximo es 20px.',
            'regimen_fiscal.max' => 'El régimen fiscal no puede exceder 100 caracteres.',
            'uso_cfdi.max' => 'El uso de CFDI no puede exceder 100 caracteres.',
            'copias.integer' => 'El número de copias debe ser un número entero.',
            'copias.min' => 'Las copias mínimo es 1.',
            'copias.max' => 'Las copias máximo es 5.',
        ];

        $validated = $request->validate([
            'tipo' => 'required|string|in:movimiento,transferencia,arqueo,cierre',
            'nombre_empresa' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:20480',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'rfc' => 'nullable|string|max:20',
            'cabecera' => 'nullable|string|max:255',
            'footer' => 'nullable|string|max:255',
            'mostrar_logo' => 'boolean',
            'mostrar_direccion' => 'boolean',
            'mostrar_telefono' => 'boolean',
            'mostrar_email' => 'boolean',
            'mostrar_rfc' => 'boolean',
            'ancho_papel' => 'nullable|string|in:58mm,80mm',
            'fuente' => 'nullable|string|in:monospace,sans-serif,serif',
            'tamano_fuente' => 'nullable|integer|min:8|max:20',
            'regimen_fiscal' => 'nullable|string|max:100',
            'uso_cfdi' => 'nullable|string|max:100',
            'mostrar_regimen' => 'boolean',
            'auto_imprimir' => 'boolean',
            'facturar' => 'boolean',
            'copias' => 'nullable|integer|min:1|max:5',
            'activo' => 'boolean',
        ], $messages);

        $exists = TicketConfiguracion::where('empresa_id', $empresaId)
            ->where('tipo', $validated['tipo'])
            ->exists();
        if ($exists) {
            $error = 'Ya existe una configuración para este tipo de ticket.';
            if ($isAjax) {
                return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => $error], 422);
            }
            return back()->withInput()->with('error', $error);
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        try {
            TicketConfiguracion::create([
                'empresa_id' => $empresaId,
                'tipo' => $validated['tipo'],
                'nombre_empresa' => $validated['nombre_empresa'] ?? null,
                'logo_url' => $logoPath,
                'direccion' => $validated['direccion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'rfc' => $validated['rfc'] ?? null,
                'cabecera' => $validated['cabecera'] ?? null,
                'footer' => $validated['footer'] ?? '¡Gracias por su compra!',
                'mostrar_logo' => $request->has('mostrar_logo'),
                'mostrar_direccion' => $request->has('mostrar_direccion'),
                'mostrar_telefono' => $request->has('mostrar_telefono'),
                'mostrar_email' => $request->has('mostrar_email'),
                'mostrar_rfc' => $request->has('mostrar_rfc'),
                'ancho_papel' => $validated['ancho_papel'] ?? '80mm',
                'fuente' => $validated['fuente'] ?? 'monospace',
                'tamano_fuente' => $validated['tamano_fuente'] ?? 12,
                'regimen_fiscal' => $validated['regimen_fiscal'] ?? null,
                'uso_cfdi' => $validated['uso_cfdi'] ?? null,
                'mostrar_regimen' => $request->has('mostrar_regimen'),
                'auto_imprimir' => $request->has('auto_imprimir'),
                'facturar' => $request->has('facturar'),
                'copias' => $validated['copias'] ?? 1,
                'activo' => $request->has('activo'),
            ]);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'icon' => 'success',
                    'title' => '¡Creado!',
                    'message' => 'Configuración de ticket creada correctamente.',
                    'redirect' => route('ticket.index')
                ]);
            }
            return redirect()->route('ticket.index')->with('success', 'Configuración de ticket creada correctamente.');
        } catch (\Exception $e) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            Log::error('Error al guardar configuración: ' . $e->getMessage());
            $error = 'Error al guardar la configuración.';
            if ($isAjax) {
                return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => $error], 500);
            }
            return back()->withInput()->with('error', $error);
        }
    }

    public function show(TicketConfiguracion $ticketConfiguracion)
    {
        $empresaId = $this->empresaActivaId();
        if ($ticketConfiguracion->empresa_id != $empresaId) {
            return redirect()->route('ticket.index')->with('error', 'No tienes permiso.');
        }
        return view('tickets.show', compact('ticketConfiguracion'));
    }

    public function edit(TicketConfiguracion $ticketConfiguracion)
    {
        $empresaId = $this->empresaActivaId();
        if ($ticketConfiguracion->empresa_id != $empresaId) {
            return redirect()->route('ticket.index')->with('error', 'No tienes permiso.');
        }
        return view('tickets.edit', compact('ticketConfiguracion'));
    }

    public function update(Request $request, TicketConfiguracion $ticketConfiguracion)
    {
        $isAjax = $request->ajax() || $request->wantsJson();
        $empresaId = $this->empresaActivaId();

        if ($ticketConfiguracion->empresa_id != $empresaId) {
            $error = 'No tienes permiso.';
            if ($isAjax) {
                return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => $error], 403);
            }
            return redirect()->route('ticket.index')->with('error', $error);
        }
        // ✅ Normalizar checkboxes: convertir "on" a 1, y si no existe a 0
        $checkboxFields = [
            'mostrar_logo',
            'mostrar_direccion',
            'mostrar_telefono',
            'mostrar_email',
            'mostrar_rfc',
            'mostrar_regimen',
            'auto_imprimir',
            'facturar',
            'activo'
        ];
        foreach ($checkboxFields as $field) {
            $request->merge([$field => $request->has($field) ? 1 : 0]);
        }

        $messages = [
            'logo.image' => 'El logo debe ser una imagen válida (jpg, jpeg, png, gif, webp).',
            'logo.max' => 'El logo no puede pesar más de 20MB.',
            'nombre_empresa.max' => 'El nombre de la empresa no puede exceder 255 caracteres.',
            'direccion.max' => 'La dirección no puede exceder 500 caracteres.',
            'telefono.max' => 'El teléfono no puede exceder 50 caracteres.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'email.max' => 'El correo no puede exceder 100 caracteres.',
            'rfc.max' => 'El RFC no puede exceder 20 caracteres.',
            'cabecera.max' => 'La cabecera no puede exceder 255 caracteres.',
            'footer.max' => 'El pie de página no puede exceder 255 caracteres.',
            'ancho_papel.in' => 'El ancho del papel debe ser 58mm o 80mm.',
            'fuente.in' => 'La fuente debe ser monospace, sans-serif o serif.',
            'tamano_fuente.integer' => 'El tamaño de fuente debe ser un número entero.',
            'tamano_fuente.min' => 'El tamaño de fuente mínimo es 8px.',
            'tamano_fuente.max' => 'El tamaño de fuente máximo es 20px.',
            'regimen_fiscal.max' => 'El régimen fiscal no puede exceder 100 caracteres.',
            'uso_cfdi.max' => 'El uso de CFDI no puede exceder 100 caracteres.',
            'copias.integer' => 'El número de copias debe ser un número entero.',
            'copias.min' => 'Las copias mínimo es 1.',
            'copias.max' => 'Las copias máximo es 5.',
            // Mensajes para los booleanos normalizados
            'mostrar_logo.boolean' => 'El campo mostrar logo debe ser sí o no.',
            'mostrar_direccion.boolean' => 'El campo mostrar dirección debe ser sí o no.',
            'mostrar_telefono.boolean' => 'El campo mostrar teléfono debe ser sí o no.',
            'mostrar_email.boolean' => 'El campo mostrar email debe ser sí o no.',
            'mostrar_rfc.boolean' => 'El campo mostrar RFC debe ser sí o no.',
            'mostrar_regimen.boolean' => 'El campo mostrar régimen debe ser sí o no.',
            'auto_imprimir.boolean' => 'El campo auto imprimir debe ser sí o no.',
            'facturar.boolean' => 'El campo facturar debe ser sí o no.',
            'activo.boolean' => 'El campo activo debe ser sí o no.',
        ];

        // Validar manualmente para capturar errores
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nombre_empresa' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'rfc' => 'nullable|string|max:20',
            'cabecera' => 'nullable|string|max:255',
            'footer' => 'nullable|string|max:255',
            'mostrar_logo' => 'boolean',
            'mostrar_direccion' => 'boolean',
            'mostrar_telefono' => 'boolean',
            'mostrar_email' => 'boolean',
            'mostrar_rfc' => 'boolean',
            'ancho_papel' => 'nullable|string|in:58mm,80mm',
            'fuente' => 'nullable|string|in:monospace,sans-serif,serif',
            'tamano_fuente' => 'nullable|integer|min:8|max:20',
            'regimen_fiscal' => 'nullable|string|max:100',
            'uso_cfdi' => 'nullable|string|max:100',
            'mostrar_regimen' => 'boolean',
            'auto_imprimir' => 'boolean',
            'facturar' => 'boolean',
            'copias' => 'nullable|integer|min:1|max:5',
            'activo' => 'boolean',
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all(); // Array de mensajes
            $errorHtml = '<ul class="text-left">' . implode('', array_map(fn($e) => "<li>• $e</li>", $errors)) . '</ul>';
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'title' => 'Errores de validación',
                    'message' => $errorHtml,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Manejo del logo (igual que antes)
        $logoPath = $ticketConfiguracion->logo_url;
        if ($request->hasFile('logo')) {
            if ($ticketConfiguracion->logo_url && Storage::disk('public')->exists($ticketConfiguracion->logo_url)) {
                Storage::disk('public')->delete($ticketConfiguracion->logo_url);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        try {
            $ticketConfiguracion->update([
                'nombre_empresa' => $validated['nombre_empresa'] ?? null,
                'logo_url' => $logoPath,
                'direccion' => $validated['direccion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'rfc' => $validated['rfc'] ?? null,
                'cabecera' => $validated['cabecera'] ?? null,
                'footer' => $validated['footer'] ?? '¡Gracias por su compra!',
                'mostrar_logo' => $request->has('mostrar_logo'),
                'mostrar_direccion' => $request->has('mostrar_direccion'),
                'mostrar_telefono' => $request->has('mostrar_telefono'),
                'mostrar_email' => $request->has('mostrar_email'),
                'mostrar_rfc' => $request->has('mostrar_rfc'),
                'ancho_papel' => $validated['ancho_papel'] ?? '80mm',
                'fuente' => $validated['fuente'] ?? 'monospace',
                'tamano_fuente' => $validated['tamano_fuente'] ?? 12,
                'regimen_fiscal' => $validated['regimen_fiscal'] ?? null,
                'uso_cfdi' => $validated['uso_cfdi'] ?? null,
                'mostrar_regimen' => $request->has('mostrar_regimen'),
                'auto_imprimir' => $request->has('auto_imprimir'),
                'facturar' => $request->has('facturar'),
                'copias' => $validated['copias'] ?? 1,
                'activo' => $request->has('activo'),
            ]);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'icon' => 'success',
                    'title' => '¡Actualizado!',
                    'message' => 'Configuración actualizada correctamente.',
                    'redirect' => route('ticket.index')
                ]);
            }
            return redirect()->route('ticket.index')->with('success', 'Configuración actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración: ' . $e->getMessage());
            $error = 'Error al actualizar la configuración.';
            if ($isAjax) {
                return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => $error], 500);
            }
            return back()->withInput()->with('error', $error);
        }
    }

    public function destroy(Request $request, TicketConfiguracion $ticketConfiguracion)
    {
        $isAjax = $request->ajax() || $request->wantsJson();
        $empresaId = $this->empresaActivaId();

        if ($ticketConfiguracion->empresa_id != $empresaId) {
            $error = 'No tienes permiso.';
            if ($isAjax) {
                return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => $error], 403);
            }
            return redirect()->route('ticket.index')->with('error', $error);
        }

        try {
            if ($ticketConfiguracion->logo_url && Storage::disk('public')->exists($ticketConfiguracion->logo_url)) {
                Storage::disk('public')->delete($ticketConfiguracion->logo_url);
            }
            $ticketConfiguracion->delete();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'icon' => 'success',
                    'title' => '¡Eliminado!',
                    'message' => 'Configuración eliminada correctamente.'
                ]);
            }
            return redirect()->route('ticket.index')->with('success', 'Configuración eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar configuración: ' . $e->getMessage());
            $error = 'Error al eliminar la configuración.';
            if ($isAjax) {
                return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => $error], 500);
            }
            return back()->with('error', $error);
        }
    }

    public function toggleActivo(Request $request, TicketConfiguracion $ticketConfiguracion)
    {
        $isAjax = $request->ajax() || $request->wantsJson();
        $empresaId = $this->empresaActivaId();

        if ($ticketConfiguracion->empresa_id != $empresaId) {
            return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => 'No autorizado'], 403);
        }

        try {
            $ticketConfiguracion->activo = !$ticketConfiguracion->activo;
            $ticketConfiguracion->save();
            return response()->json([
                'success' => true,
                'icon' => 'success',
                'title' => $ticketConfiguracion->activo ? 'Activado' : 'Desactivado',
                'message' => $ticketConfiguracion->activo ? 'Configuración activada' : 'Configuración desactivada',
                'activo' => $ticketConfiguracion->activo
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'icon' => 'error', 'title' => 'Error', 'message' => 'Error al cambiar estado'], 500);
        }
    }

    /**
     * Vista de diseño / previsualización del ticket
     */
    public function diseno()
    {
        $empresaId = $this->empresaActivaId();
        if (!$empresaId) {
            return redirect()->route('dashboard')->with('error', 'No hay una empresa activa.');
        }

        // Obtener todas las configuraciones para mostrarlas en un selector o la primera
        $configuraciones = TicketConfiguracion::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('tipo')
            ->get();

        // Si hay configuraciones, tomamos la primera para previsualizar
        $config = $configuraciones->first();

        return view('tickets.diseno', compact('configuraciones', 'config'));
    }
}