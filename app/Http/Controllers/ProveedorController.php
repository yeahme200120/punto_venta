<?php
// app/Http/Controllers/ProveedorController.php
namespace App\Http\Controllers;

use App\Exports\ProveedoresExport;
use App\Models\Empresa;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProveedorController extends Controller
{
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    private function verificarEmpresa(Proveedor $proveedor)
    {
        $empresaId = $this->empresaActivaId();
        if (auth()->user()->hasRole('Super Admin') && !$empresaId) {
            return;
        }
        if ($proveedor->empresa_id !== $empresaId) {
            abort(403, 'Este proveedor no pertenece a la empresa activa.');
        }
    }

    public function index(Request $request)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresaActiva = Empresa::find($empresaId);

            $query = Proveedor::where('empresa_id', $empresaId);

            // Búsqueda
            if ($request->filled('search')) {
                $query->buscar($request->search);
            }

            $proveedores = $query->orderBy('nombre')
                ->paginate(10)
                ->withQueryString();

            return view('proveedores.index', compact('proveedores', 'empresaActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar proveedores: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la lista de proveedores.');
        }
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20|unique:proveedors,rfc,NULL,id,empresa_id,' . $this->empresaActivaId(),
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'rfc.unique' => 'Este RFC ya está registrado para esta empresa.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
        ]);

        DB::beginTransaction();
        try {
            $proveedor = Proveedor::create([
                'empresa_id' => $this->empresaActivaId(),
                'nombre' => $validated['nombre'],
                'rfc' => strtoupper($validated['rfc'] ?? null),
                'telefono' => $validated['telefono'] ?? null,
                'correo' => $validated['correo'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'activo' => true,
            ]);

            DB::commit();

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor "' . $proveedor->nombre . '" creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear proveedor: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el proveedor: ' . $e->getMessage());
        }
    }

    public function show(Proveedor $proveedor)
    {
        try {
            $this->verificarEmpresa($proveedor);
            $proveedor->load([
                'productos' => function ($q) {
                    $q->limit(10);
                },
                'insumos' => function ($q) {
                    $q->limit(10);
                }
            ]);
            return view('proveedores.show', compact('proveedor'));
        } catch (\Exception $e) {
            Log::error('Error al mostrar proveedor: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos del proveedor.');
        }
    }

    public function edit(Proveedor $proveedor)
    {
        try {
            $this->verificarEmpresa($proveedor);
            return view('proveedores.edit', compact('proveedor'));
        } catch (\Exception $e) {
            Log::error('Error al cargar edición: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $this->verificarEmpresa($proveedor);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20|unique:proveedors,rfc,' . $proveedor->id . ',id,empresa_id,' . $proveedor->empresa_id,
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'rfc.unique' => 'Este RFC ya está registrado para esta empresa.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
        ]);

        DB::beginTransaction();
        try {
            $proveedor->update([
                'nombre' => $validated['nombre'],
                'rfc' => strtoupper($validated['rfc'] ?? null),
                'telefono' => $validated['telefono'] ?? null,
                'correo' => $validated['correo'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'activo' => $request->has('activo') ? 1 : 0,
            ]);

            DB::commit();

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor "' . $proveedor->nombre . '" actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar proveedor: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el proveedor: ' . $e->getMessage());
        }
    }

    public function destroy(Proveedor $proveedor)
    {
        $this->verificarEmpresa($proveedor);
        $isAjax = request()->ajax() || request()->wantsJson();

        if ($proveedor->productos()->count() > 0 || $proveedor->insumos()->count() > 0) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene productos o insumos asociados.'
                ], 422);
            }
            return back()->with('error', 'No se puede eliminar porque tiene productos o insumos asociados.');
        }

        DB::beginTransaction();
        try {
            $nombre = $proveedor->nombre;
            $proveedor->delete();
            DB::commit();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Proveedor "' . $nombre . '" eliminado correctamente.'
                ]);
            }

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor "' . $nombre . '" eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar proveedor: ' . $e->getMessage());

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el proveedor.'
                ], 500);
            }
            return back()->with('error', 'Error al eliminar el proveedor.');
        }
    }

    public function export()
    {
        try {
            $empresaId = $this->empresaActivaId();
            $empresa = Empresa::find($empresaId);

            if (!$empresa) {
                return back()->with('error', 'No se encontró la empresa activa.');
            }

            $fileName = 'proveedores_' . str_replace(' ', '_', $empresa->nombre) . '_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new ProveedoresExport($empresaId), $fileName);

        } catch (\Exception $e) {
            Log::error('Error al exportar proveedores: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel.');
        }
    }
    /**
     * Reactivar proveedor
     */
    public function reactivar($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $this->verificarEmpresa($proveedor);

            if (!auth()->user()->can('editar_proveedores') && !auth()->user()->hasRole('Super Admin')) {
                return response()->json(['success' => false, 'message' => 'No tienes permisos'], 403);
            }

            $proveedor->update(['activo' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor "' . $proveedor->nombre . '" reactivado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al reactivar proveedor: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al reactivar'], 500);
        }
    }
    public function toggleActivo($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);

            // ✅ Solo validar si está activo y tiene productos
            if ($proveedor->activo && $proveedor->productos->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar porque tiene ' . $proveedor->productos->count() . ' producto(s) asociado(s)'
                ], 422);
            }

            $proveedor->activo = !$proveedor->activo;
            $proveedor->save();

            $mensaje = $proveedor->activo ? 'Proveedor reactivado correctamente' : 'Proveedor desactivado correctamente';

            return response()->json([
                'success' => true,
                'message' => $mensaje
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function desactivar($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $this->verificarEmpresa($proveedor);

            if (!auth()->user()->can('editar_proveedores') && !auth()->user()->hasRole('Super Admin')) {
                return response()->json(['success' => false, 'message' => 'No tienes permisos'], 403);
            }

            // ✅ Validar productos e insumos
            $totalAsociados = $proveedor->productos()->count() + $proveedor->insumos()->count();
            if ($totalAsociados > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar porque tiene ' . $totalAsociados . ' producto(s) o insumo(s) asociado(s)'
                ], 422);
            }

            $proveedor->update(['activo' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor "' . $proveedor->nombre . '" desactivado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al desactivar proveedor: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al desactivar'], 500);
        }
    }

}