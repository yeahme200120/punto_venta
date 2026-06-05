<?php

namespace App\Http\Controllers;

use App\Exports\ClientesExport;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    /**
     * Listado de clientes
     */
    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresaActiva = Empresa::find($empresaId);

            $clientes = Cliente::with('sucursal')
                ->where('empresa_id', $empresaId)
                ->orderBy('nombre')
                ->paginate(10)
                ->withQueryString();

            return view('clientes.index', compact('clientes', 'empresaActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar clientes: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de clientes.');
        }
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $empresaId = $this->empresaActivaId();
        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('clientes.create', compact('sucursales'));
    }

    /**
     * Almacenar nuevo cliente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'tipo' => 'required|in:contado,credito',
            'limite_credito' => 'nullable|numeric|min:0',
            'dias_credito' => 'nullable|integer|min:0',
            'sucursal_id' => 'nullable|integer',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'tipo.required' => 'Seleccione el tipo de cliente.',
            'tipo.in' => 'Tipo de cliente no válido.',
        ]);

        DB::beginTransaction();
        try {
            Cliente::create([
                'empresa_id' => $this->empresaActivaId(),
                'sucursal_id' => $validated['sucursal_id'] ?? null,
                'nombre' => $validated['nombre'],
                'rfc' => $validated['rfc'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'correo' => $validated['correo'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'tipo' => $validated['tipo'],
                'limite_credito' => $validated['limite_credito'] ?? 0,
                'dias_credito' => $validated['dias_credito'] ?? 0,
                'activo' => true,
            ]);

            DB::commit();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente "' . $validated['nombre'] . '" creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el cliente. Intente nuevamente.');
        }
    }

    /**
     * Mostrar detalle de cliente
     */
    public function show(Cliente $cliente)
    {
        $cliente->load(['sucursal', 'empresa']);
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Cliente $cliente)
    {
        $empresaId = $this->empresaActivaId();
        $sucursales = Sucursal::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('clientes.edit', compact('cliente', 'sucursales'));
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'tipo' => 'required|in:contado,credito',
            'limite_credito' => 'nullable|numeric|min:0',
            'dias_credito' => 'nullable|integer|min:0',
            'sucursal_id' => 'nullable|integer',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
        ]);

        DB::beginTransaction();
        try {
            $cliente->update([
                'sucursal_id' => $validated['sucursal_id'] ?? null,
                'nombre' => $validated['nombre'],
                'rfc' => $validated['rfc'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'correo' => $validated['correo'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'tipo' => $validated['tipo'],
                'limite_credito' => $validated['limite_credito'] ?? 0,
                'dias_credito' => $validated['dias_credito'] ?? 0,
                'activo' => $request->has('activo'),
            ]);

            DB::commit();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente "' . $cliente->nombre . '" actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el cliente.');
        }
    }

    /**
     * Eliminar cliente
     */
    public function destroy(Cliente $cliente)
    {
        DB::beginTransaction();
        try {
            $nombre = $cliente->nombre;
            $cliente->delete();
            DB::commit();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente "' . $nombre . '" eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el cliente.');
        }
    }

    /**
     * Exportar clientes a Excel
     */
    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = \App\Models\Empresa::find($empresaId);

            if (!$empresa) {
                return back()->with('error', 'No se encontró la empresa activa.');
            }

            $fileName = 'clientes_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new ClientesExport($empresaId), $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar clientes: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
}