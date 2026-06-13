<?php

namespace App\Http\Controllers;

use App\Exports\EmpresasExport;
use App\Models\Empresa;
use App\Models\EmpresaFormaPago;
use App\Models\EmpresaLicenciaHistorial;
use App\Models\FormaPago;
use App\Models\Licencia;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;

class EmpresaController extends Controller
{
    /**
     * Cambiar empresa activa (solo Super Admin)
     */
    public function cambiar(Empresa $empresa)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $primeraSucursal = $empresa->sucursales()->where('activo', true)->first();

            session([
                'empresa_activa_id' => $empresa->id,
                'empresa_activa_nombre' => $empresa->nombre,
                'sucursal_activa_id' => $primeraSucursal?->id,
                'sucursal_activa_nombre' => $primeraSucursal?->nombre,
            ]);

            return redirect()->back()->with('success', "Empresa cambiada a: {$empresa->nombre}");

        } catch (\Exception $e) {
            Log::error('Error al cambiar empresa: ' . $e->getMessage());
            return back()->with('error', 'Error al cambiar de empresa.');
        }
    }

    /**
     * Listar empresas (solo Super Admin)
     */
    public function index()
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $empresas = Empresa::with(['licencia', 'sucursales', 'usuarios'])
                ->orderBy('nombre')
                ->paginate(10)
                ->withQueryString();

            return view('empresas.index', compact('empresas'));

        } catch (\Exception $e) {
            Log::error('Error al listar empresas: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de empresas.');
        }
    }

    /**
     * Formulario crear empresa
     */
    public function create()
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $licencias = Licencia::where('activo', true)
                ->orderBy('dias')
                ->get();

            return view('empresas.create', compact('licencias'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de empresa: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Guardar empresa
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        $validated = $request->validate(
            [
                'licencia_id' => 'required|integer',
                'nombre' => 'required|string|max:255',
                'rfc' => 'required|unique:empresas,rfc',
                'direccion' => 'nullable|string|max:500',
                'telefono' => 'nullable|string|max:20',
                'correo' => 'nullable|email|max:255',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ],
            [
                'licencia_id.required' => 'Debe seleccionar una licencia.',
                'licencia_id.integer' => 'La licencia seleccionada no es válida.',

                'nombre.required' => 'El nombre de la empresa es obligatorio.',
                'nombre.max' => 'El nombre de la empresa no puede exceder 255 caracteres.',

                'rfc.required' => 'El RFC es obligatorio.',
                'rfc.unique' => 'Ya existe una empresa registrada con este RFC.',

                'direccion.max' => 'La dirección no puede exceder 500 caracteres.',

                'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',

                'correo.email' => 'Debe ingresar un correo electrónico válido.',
                'correo.max' => 'El correo electrónico no puede exceder 255 caracteres.',

                'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
                'fecha_inicio.date' => 'La fecha de inicio no tiene un formato válido.',

                'fecha_fin.required' => 'La fecha de vencimiento es obligatoria.',
                'fecha_fin.date' => 'La fecha de vencimiento no tiene un formato válido.',
                'fecha_fin.after' => 'La fecha de vencimiento debe ser posterior a la fecha de inicio.',

                'logo.image' => 'El archivo seleccionado debe ser una imagen.',
                'logo.mimes' => 'El logo debe ser un archivo JPG, JPEG, PNG, GIF o WEBP.',
                'logo.max' => 'El logo no puede superar los 2 MB de tamaño.',
            ]
        );

        DB::beginTransaction();
        try {
            // Calcular fecha_fin correctamente: fecha_inicio + días de la licencia
            $licencia = Licencia::find($validated['licencia_id']);
            $fechaFin = \Carbon\Carbon::parse($validated['fecha_inicio'])->addDays($licencia->dias);
            // Crear empresa primero para tener el ID
            $empresa = Empresa::create([
                'licencia_id' => $validated['licencia_id'],
                'nombre' => $validated['nombre'],
                'rfc' => strtoupper($validated['rfc']),
                'direccion' => $validated['direccion'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $fechaFin,
                'activo' => true,
            ]);

            // Manejar la carga del logo en carpeta específica
            if ($request->hasFile('logo')) {
                $this->handleLogoUpload($request->file('logo'), $empresa);
            }

            // 🔥 INICIALIZAR FORMAS DE PAGO PARA LA NUEVA EMPRESA
            $this->inicializarFormasPago($empresa->id);

            DB::commit();

            return redirect()->route('empresas.index')
                ->with('success', 'Empresa "' . $empresa->nombre . '" creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear empresa: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear la empresa: ' . $e->getMessage());
        }
    }
    private function inicializarFormasPago($empresaId)
    {
        // Obtener formas de pago activas globalmente
        $formasActivasGlobal = FormaPago::where('activo_global', true)->get();

        foreach ($formasActivasGlobal as $forma) {
            EmpresaFormaPago::create([
                'empresa_id' => $empresaId,
                'forma_pago_id' => $forma->id,
                'activo' => true,
                'orden_empresa' => $forma->orden
            ]);
        }

        Log::info("Formas de pago inicializadas para empresa ID: {$empresaId}");
    }
    /**
     * Ver empresa con sucursales
     */
    public function show(Empresa $empresa)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $empresa->load(['licencia', 'sucursales', 'usuarios.roles']);
            return view('empresas.show', compact('empresa'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar empresa: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos de la empresa.');
        }
    }

    /**
     * Formulario editar empresa
     */
    public function edit(Empresa $empresa)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $licencias = Licencia::where('activo', true)
                ->orderBy('dias')
                ->get();

            return view('empresas.edit', compact('empresa', 'licencias'));

        } catch (\Exception $e) {
            Log::error('Error al cargar edición de empresa: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualizar empresa
     */
    public function update(Request $request, Empresa $empresa)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        $validated = $request->validate([
            'licencia_id' => 'required|integer',
            'nombre' => 'required|string|max:255',
            'rfc' => 'required|unique:empresas,rfc,' . $empresa->id,
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            // Mensajes de error en español
            'licencia_id.required' => 'Debe seleccionar un tipo de licencia.',
            'licencia_id.integer' => 'La licencia seleccionada no es válida.',
            'nombre.required' => 'El nombre de la empresa es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.unique' => 'Este RFC ya está registrado en otra empresa.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_fin.after' => 'La fecha de vencimiento debe ser posterior a la fecha de inicio.',
            'logo.image' => 'El archivo debe ser una imagen válida (JPG, PNG, GIF, WEBP).',
            'logo.mimes' => 'El logo debe ser un archivo de tipo: jpeg, png, jpg, gif, webp.',
            'logo.max' => 'El logo no debe pesar más de 2MB.',
        ]);

        DB::beginTransaction();
        try {
            $empresa->update([
                'licencia_id' => $validated['licencia_id'],
                'nombre' => $validated['nombre'],
                'rfc' => strtoupper($validated['rfc']),
                'direccion' => $validated['direccion'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'activo' => $request->has('activo'),
            ]);

            // Manejar la carga del nuevo logo
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior
                if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                    Storage::disk('public')->delete($empresa->logo);
                }
                $this->handleLogoUpload($request->file('logo'), $empresa);
            }

            DB::commit();

            return redirect()->route('empresas.index')
                ->with('success', 'Empresa "' . $empresa->nombre . '" actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar empresa: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la empresa: ' . $e->getMessage());
        }
    }

    /**
     * Exportar empresas a Excel
     */
    public function export()
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $fileName = 'empresas_' . date('Y-m-d_His') . '.xlsx';
            return Excel::download(new EmpresasExport, $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar empresas: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
    /**
     * Manejar la carga del logo
     */
    private function handleLogoUpload($file, Empresa $empresa)
    {
        try {
            // Crear nombre de carpeta limpio para la empresa
            $folderName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($empresa->nombre));
            $folderPath = "empresas/{$folderName}_{$empresa->id}";

            // Generar nombre único para el archivo
            $extension = $file->getClientOriginalExtension();
            $fileName = "logo.{$extension}";
            $fullPath = "{$folderPath}/{$fileName}";

            // Crear el directorio si no existe
            if (!Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->makeDirectory($folderPath);
            }

            // Guardar y optimizar la imagen con Intervention Image 3.x
            $imageManager = new ImageManager(new Driver());
            $image = $imageManager->read($file);

            // Redimensionar a 200x200 (cover mantiene proporción y recorta)
            $image->cover(200, 200);

            // Guardar la imagen optimizada
            $imagePath = storage_path('app/public/' . $fullPath);
            $image->save($imagePath, 85); // Calidad 85%

            // Actualizar el modelo con la ruta
            $empresa->update(['logo' => $fullPath]);

            return $fullPath;

        } catch (\Exception $e) {
            Log::error('Error al procesar logo: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
    /**
     * Mostrar formulario de renovación de licencia
     */
    public function renovarLicencia(Empresa $empresa)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $licencias = Licencia::where('activo', true)->orderBy('dias')->get();

        // 🔥 OBTENER LICENCIA ACTIVA DESDE EL MÉTODO DEL MODELO
        $licenciaActiva = $empresa->licenciaActiva();

        // Calcular días restantes usando el método del modelo
        $diasRestantes = $empresa->diasRestantesLicencia();

        $historial = $empresa->historialLicencias()->with('licencia')->get();

        return view('empresas.licencias', compact('empresa', 'licencias', 'licenciaActiva', 'diasRestantes', 'historial'));
    }

    /**
     * Procesar renovación de licencia
     */
    public function procesarRenovacionLicencia(Request $request, Empresa $empresa)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'licencia_id' => 'required|exists:licencias,id',
            'fecha_inicio_periodo' => 'required|date',
            'fecha_fin_periodo' => 'required|date|after_or_equal:fecha_inicio_periodo',
            'monto_pagado' => 'required|numeric|min:0',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calcular fecha_fin correctamente: fecha_inicio_periodo + días de la licencia
            $licencia = Licencia::find($validated['licencia_id']);
            $fechaFinPeriodo = Carbon::parse($validated['fecha_inicio_periodo'])->addDays($licencia->dias) ->endOfDay();
            // Guardar en historial
            EmpresaLicenciaHistorial::create([
                'empresa_id' => $empresa->id,
                'licencia_id' => $validated['licencia_id'],
                'fecha_inicio_original' => $empresa->fecha_inicio,
                'fecha_inicio_periodo' => $validated['fecha_inicio_periodo'],
                'fecha_fin_periodo' => $fechaFinPeriodo,
                'monto_pagado' => $validated['monto_pagado'],
                'referencia_pago' => $validated['referencia_pago'],
                'observaciones' => $validated['observaciones'],
            ]);

            // 🔥 Actualizar la licencia activa de la empresa (para fácil acceso)
            $licencia = Licencia::find($validated['licencia_id']);
            $empresa->update([
                'licencia_id' => $licencia->id,
                'fecha_inicio' => $empresa->fecha_inicio, // No cambiar la original
                'fecha_fin' => $fechaFinPeriodo,
            ]);

            DB::commit();

            return redirect()->route('empresas.show', $empresa)
                ->with('success', 'Licencia renovada correctamente. Nueva vigencia hasta: ' . date('d/m/Y', strtotime($validated['fecha_fin_periodo'])));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al renovar licencia: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al renovar la licencia: ' . $e->getMessage());
        }
    }
}