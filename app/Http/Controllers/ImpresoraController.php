<?php

namespace App\Http\Controllers;

use App\Exports\ImpresorasExport;
use App\Models\Impresora;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImpresoraController extends Controller
{
    /**
     * Obtener el ID de la empresa activa
     */
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    /**
     * Listado de impresoras
     */
    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresaActiva = \App\Models\Empresa::find($empresaId);

            $impresoras = Impresora::with('sucursal')
                ->where('empresa_id', $empresaId)
                ->orderBy('nombre')
                ->paginate(10)
                ->withQueryString();

            return view('impresoras.index', compact('impresoras', 'empresaActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar impresoras: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de impresoras.');
        }
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            return view('impresoras.create', compact('sucursales'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de impresora: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Almacenar nueva impresora
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ticket,factura,etiqueta',
            'sucursal_id' => 'nullable|integer',
            'puerto' => 'nullable|string|max:50',
            'ip' => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre de la impresora es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'tipo.required' => 'Debe seleccionar el tipo de impresora.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'ip.max' => 'La dirección IP no debe exceder los 50 caracteres.',
        ]);

        DB::beginTransaction();
        try {
            Impresora::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $validated['sucursal_id'] ?? null,
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'],
                'puerto' => $validated['puerto'] ?? null,
                'ip' => $validated['ip'] ?? null,
                'activo' => true,
            ]);

            DB::commit();

            return redirect()->route('impresoras.index')
                ->with('success', 'Impresora "' . $validated['nombre'] . '" registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear impresora: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al registrar la impresora. Intente nuevamente.');
        }
    }

    /**
     * Mostrar detalle de impresora
     */
    public function show(Impresora $impresora)
    {
        try {
            $impresora->load('sucursal');
            return view('impresoras.show', compact('impresora'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar impresora: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos de la impresora.');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(Impresora $impresora)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            return view('impresoras.edit', compact('impresora', 'sucursales'));

        } catch (\Exception $e) {
            Log::error('Error al cargar edición de impresora: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualizar impresora
     */
    public function update(Request $request, Impresora $impresora)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ticket,factura,etiqueta',
            'sucursal_id' => 'nullable|integer',
            'puerto' => 'nullable|string|max:50',
            'ip' => 'nullable|string|max:50',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre de la impresora es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'tipo.required' => 'Debe seleccionar el tipo de impresora.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
        ]);

        DB::beginTransaction();
        try {
            $impresora->update([
                'sucursal_id' => $validated['sucursal_id'] ?? null,
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'],
                'puerto' => $validated['puerto'] ?? null,
                'ip' => $validated['ip'] ?? null,
                'activo' => $request->has('activo'),
            ]);

            DB::commit();

            return redirect()->route('impresoras.index')
                ->with('success', 'Impresora "' . $impresora->nombre . '" actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar impresora: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la impresora.');
        }
    }

    /**
     * Eliminar impresora
     */
    public function destroy(Impresora $impresora)
    {
        DB::beginTransaction();
        try {
            $nombre = $impresora->nombre;
            $impresora->delete();
            DB::commit();

            return redirect()->route('impresoras.index')
                ->with('success', 'Impresora "' . $nombre . '" eliminada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar impresora: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la impresora.');
        }
    }

    /**
     * Exportar impresoras a Excel
     */
    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = \App\Models\Empresa::find($empresaId);

            if (!$empresa) {
                return back()->with('error', 'No se encontró la empresa activa.');
            }

            $fileName = 'impresoras_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new ImpresorasExport($empresaId), $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar impresoras: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
}