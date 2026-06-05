<?php

namespace App\Http\Controllers;

use App\Exports\LicenciasExport;
use App\Models\Licencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;

class LicenciaController extends Controller
{
    /**
     * Listado de licencias (Solo Super Admin)
     */
    public function index()
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $licencias = Licencia::withCount('empresas')
                ->orderBy('dias')
                ->paginate(10)
                ->withQueryString();

            return view('licencias.index', compact('licencias'));

        } catch (\Exception $e) {
            Log::error('Error al listar licencias: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de licencias.');
        }
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        return view('licencias.create');
    }

    /**
     * Almacenar nueva licencia
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dias' => 'required|integer|min:1',
            'max_usuarios' => 'required|integer|min:1',
            'max_sucursales' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0',
        ], [
            'nombre.required' => 'El nombre de la licencia es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'dias.required' => 'El número de días es obligatorio.',
            'dias.min' => 'Los días deben ser al menos 1.',
            'max_usuarios.required' => 'El máximo de usuarios es obligatorio.',
            'max_usuarios.min' => 'Debe permitir al menos 1 usuario.',
            'max_sucursales.required' => 'El máximo de sucursales es obligatorio.',
            'max_sucursales.min' => 'El mínimo de sucursales es 0.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.min' => 'El precio no puede ser negativo.',
        ]);

        DB::beginTransaction();
        try {
            Licencia::create([
                'nombre' => $validated['nombre'],
                'dias' => $validated['dias'],
                'max_usuarios' => $validated['max_usuarios'],
                'max_sucursales' => $validated['max_sucursales'],
                'precio' => $validated['precio'],
                'activo' => true,
            ]);

            DB::commit();

            return redirect()->route('licencias.index')
                ->with('success', 'Licencia "' . $validated['nombre'] . '" creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear licencia: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear la licencia. Intente nuevamente.');
        }
    }

    /**
     * Mostrar detalle de licencia
     */
    public function show(Licencia $licencia)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $licencia->load('empresas');
            return view('licencias.show', compact('licencia'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar licencia: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos de la licencia.');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(Licencia $licencia)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        return view('licencias.edit', compact('licencia'));
    }

    /**
     * Actualizar licencia
     */
    public function update(Request $request, Licencia $licencia)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dias' => 'required|integer|min:1',
            'max_usuarios' => 'required|integer|min:1',
            'max_sucursales' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre de la licencia es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'dias.required' => 'El número de días es obligatorio.',
            'dias.min' => 'Los días deben ser al menos 1.',
            'max_usuarios.required' => 'El máximo de usuarios es obligatorio.',
            'max_usuarios.min' => 'Debe permitir al menos 1 usuario.',
            'max_sucursales.required' => 'El máximo de sucursales es obligatorio.',
            'max_sucursales.min' => 'El mínimo de sucursales es 0.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.min' => 'El precio no puede ser negativo.',
        ]);

        DB::beginTransaction();
        try {
            $licencia->update([
                'nombre' => $validated['nombre'],
                'dias' => $validated['dias'],
                'max_usuarios' => $validated['max_usuarios'],
                'max_sucursales' => $validated['max_sucursales'],
                'precio' => $validated['precio'],
                'activo' => $request->has('activo'),
            ]);

            DB::commit();

            return redirect()->route('licencias.index')
                ->with('success', 'Licencia "' . $licencia->nombre . '" actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar licencia: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la licencia. Intente nuevamente.');
        }
    }

    /**
     * Eliminar licencia
     */
    public function destroy(Licencia $licencia)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        if ($licencia->empresas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: hay ' . $licencia->empresas()->count() . ' empresas usando esta licencia.');
        }

        DB::beginTransaction();
        try {
            $nombre = $licencia->nombre;
            $licencia->delete();
            DB::commit();

            return redirect()->route('licencias.index')
                ->with('success', 'Licencia "' . $nombre . '" eliminada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar licencia: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la licencia.');
        }
    }

    /**
     * Exportar licencias a Excel
     */
    public function export()
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Acción no autorizada.');
        }

        try {
            $fileName = 'licencias_' . date('Y-m-d_His') . '.xlsx';
            return Excel::download(new LicenciasExport, $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar licencias: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
}