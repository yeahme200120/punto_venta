<?php

namespace App\Http\Controllers;

use App\Exports\ClientesExport;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Traits\ActivaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{
    use ActivaTrait;
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

    public function create()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            return view('clientes.create', compact('sucursales'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de creación: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el formulario.');
        }
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
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'tipo.required' => 'Seleccione el tipo de cliente.',
            'tipo.in' => 'Tipo de cliente no válido.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
        ]);

        DB::beginTransaction();
        try {
            $cliente = Cliente::create([
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

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'message' => 'Cliente "' . $cliente->nombre . '" creado correctamente.',
                'status' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error al crear el cliente: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    /**
     * Mostrar detalle de cliente
     */
    public function show(Cliente $cliente)
    {
        try {
            $cliente->load(['sucursal', 'empresa']);
            return view('clientes.show', compact('cliente'));
        } catch (\Exception $e) {
            Log::error('Error al mostrar cliente: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar los detalles del cliente.');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(Cliente $cliente)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $sucursales = Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            return view('clientes.edit', compact('cliente', 'sucursales'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al cargar el formulario de edición.');
        }
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
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
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
                'activo' => $request->has('activo') ? true : false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'icon' => 'success',
                'message' => 'Cliente "' . $cliente->nombre . '" actualizado correctamente.',
                'status' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    /**
     * Desactivar cliente (cambiar activo a 0)
     */
    public function destroy($id)
    {
        try {
            // Verificar permisos - Solo Super Admin y Administrador
            if (!auth()->user()->hasRole(['Super Admin', 'Administrador'])) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'message' => 'No tienes permisos para desactivar clientes',
                    'status' => 403
                ], 403);
            }

            $cliente = Cliente::findOrFail($id);
            
            // Verificar si ya está inactivo
            if ($cliente->activo == 0) {
                return response()->json([
                    'success' => false,
                    'icon' => 'warning',
                    'message' => 'Este cliente ya está inactivo',
                    'status' => 400
                ], 400);
            }
            
            // Solo desactivar, no eliminar
            $cliente->activo = 0;
            $cliente->save();
            
            return response()->json([
                'success' => true,
                'icon' => 'success',
                'message' => 'Cliente "' . $cliente->nombre . '" desactivado correctamente',
                'status' => 200
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al desactivar cliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error al desactivar el cliente: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
    
    /**
     * Reactivar cliente (cambiar activo a 1)
     */
    public function reactivar($id)
    {
        try {
            // Verificar permisos - Solo Super Admin y Administrador
            if (!auth()->user()->hasRole(['Super Admin', 'Administrador'])) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'message' => 'No tienes permisos para reactivar clientes',
                    'status' => 403
                ], 403);
            }

            $cliente = Cliente::findOrFail($id);
            
            // Verificar si ya está activo
            if ($cliente->activo == 1) {
                return response()->json([
                    'success' => false,
                    'icon' => 'warning',
                    'message' => 'Este cliente ya está activo',
                    'status' => 400
                ], 400);
            }
            
            // Reactivar cliente
            $cliente->activo = 1;
            $cliente->save();
            
            return response()->json([
                'success' => true,
                'icon' => 'success',
                'message' => 'Cliente "' . $cliente->nombre . '" reactivado correctamente',
                'status' => 200
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al reactivar cliente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error al reactivar el cliente: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    /**
     * Exportar clientes a Excel
     */
    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = Empresa::find($empresaId);

            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'icon' => 'error',
                    'message' => 'No se encontró la empresa activa.',
                    'status' => 404
                ], 404);
            }

            $fileName = 'clientes_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new ClientesExport($empresaId), $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar clientes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'icon' => 'error',
                'message' => 'Error al generar el archivo Excel: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}