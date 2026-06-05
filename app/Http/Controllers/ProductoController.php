<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Proveedor;
use App\Models\Insumo;
use App\Models\InventarioMovimiento;
use App\Models\ProductoImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function __construct()
    {
        error_log('=== Constructor de ProductoController ejecutado ===');
    }
    /**
     * Obtener el ID de la empresa activa desde la sesión
     */
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            // Agregar valor por defecto igual que en UsuarioController
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    /**
     * Obtener el ID de la sucursal activa desde la sesión
     */
    private function sucursalActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('sucursal_activa_id');
        }
        return auth()->user()->sucursal_id;
    }

    /**
     * Verificar que el producto pertenece a la empresa activa
     */
    private function verificarEmpresa(Producto $producto)
    {
        $empresaId = $this->empresaActivaId();

        // Super Admin puede ver productos de cualquier empresa si tiene una seleccionada
        if (auth()->user()->hasRole('Super Admin') && !$empresaId) {
            return; // Super Admin sin empresa seleccionada puede ver todo
        }

        if ($producto->empresa_id !== $empresaId) {
            abort(403, 'Este producto no pertenece a la empresa activa.');
        }
    }

    /**
     * Listado de productos con paginación
     */
    public function index()
    {
        try {
            $empresaId = $this->empresaActivaId();

            $query = Producto::with(['categoria', 'proveedores', 'insumos']);

            // Filtrar por empresa si no es Super Admin o si tiene empresa seleccionada
            if (!auth()->user()->hasRole('Super Admin')) {
                $query->where('empresa_id', $empresaId);
            } elseif ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }

            $productos = $query->orderBy('nombre')
                ->paginate(15)
                ->withQueryString();

            $empresaActiva = $empresaId ? Empresa::find($empresaId) : null;
            $sucursalActiva = $this->sucursalActivaId() ? \App\Models\Sucursal::find($this->sucursalActivaId()) : null;

            return view('productos.index', compact('productos', 'empresaActiva', 'sucursalActiva'));

        } catch (\Exception $e) {
            Log::error('Error al listar productos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los productos. Intente nuevamente.');
        }
    }
    public function create()
    {
        try {
            $empresaId = $this->empresaActivaId();

            // Para Super Admin sin empresa seleccionada
            if (auth()->user()->hasRole('Super Admin') && !$empresaId) {
                $empresas = Empresa::where('activo', true)->orderBy('nombre')->get();
                $categorias = collect();
                $proveedores = collect();
                $insumos = collect();
                $productosRelacionados = collect();

                return view('productos.create', compact('categorias', 'proveedores', 'insumos', 'empresas', 'empresaId', 'productosRelacionados'));
            }

            if (!$empresaId) {
                return redirect()->route('dashboard')->with('error', 'No hay una empresa seleccionada.');
            }

            $empresas = auth()->user()->hasRole('Super Admin')
                ? Empresa::where('activo', true)->orderBy('nombre')->get()
                : collect();

            $categorias = Categoria::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            // Verificar permisos para mostrar proveedores
            $proveedores = collect();
            if (auth()->user()->can('ver_proveedores') || auth()->user()->hasRole('Super Admin')) {
                $proveedores = Proveedor::where('empresa_id', $empresaId)
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();
            }

            // Verificar permisos para mostrar insumos
            $insumos = collect();
            if (auth()->user()->can('ver_insumos') || auth()->user()->hasRole('Super Admin')) {
                $insumos = Insumo::where('empresa_id', $empresaId)
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();
            }

            $productosRelacionados = Producto::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'precio_venta']);

            // Generar códigos automáticos
            $codigoBarras = Producto::generarCodigoBarras();
            $sku = Producto::generarSKU();

            return view('productos.create', compact('categorias', 'proveedores', 'insumos', 'empresas', 'empresaId', 'productosRelacionados', 'codigoBarras', 'sku'));

        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de producto: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario. Intente nuevamente.');
        }
    }
    public function store(Request $request)
    {
        // LOG DE DEPURACIÓN - Corregido
        Log::info('=== INICIO STORE PRODUCTO ===');
        Log::info('Datos recibidos:', ['data' => $request->all()]);
        Log::info('Archivos recibidos:', [
            'has_files' => $request->hasFile('imagenes'),
            'count' => $request->hasFile('imagenes') ? count($request->file('imagenes')) : 0
        ]);

        $validated = $request->validate([
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo_barras' => 'nullable|string|max:100',
            'sku' => 'required|string|max:50|unique:productos,sku',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0|gte:costo_compra',
            'stock' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0|lt:stock_maximo',
            'stock_maximo' => 'required|numeric|min:0|gt:stock_minimo',
            'control_inventario' => 'sometimes|boolean',
            'activo' => 'sometimes|boolean',
            'proveedores' => 'nullable|array',
            'insumos' => 'nullable|array',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Determinar la empresa
            if (auth()->user()->hasRole('Super Admin')) {
                $empresaId = $request->empresa_id ?? $this->empresaActivaId();
                if (!$empresaId) {
                    throw new \Exception('Debe seleccionar una empresa.');
                }
            } else {
                $empresaId = $this->empresaActivaId();
            }

            Log::info('Empresa ID:', ['id' => $empresaId]);

            // Generar códigos si no se enviaron
            $codigoBarras = $request->codigo_barras ?: Producto::generarCodigoBarras();
            $sku = $request->sku ?: Producto::generarSKU();

            $producto = Producto::create([
                'empresa_id' => $empresaId,
                'categoria_id' => $validated['categoria_id'] ?? null,
                'codigo_barras' => $codigoBarras,
                'sku' => $sku,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'costo_compra' => $validated['costo_compra'],
                'precio_venta' => $validated['precio_venta'],
                'stock' => $validated['stock'],
                'stock_minimo' => $validated['stock_minimo'],
                'stock_maximo' => $validated['stock_maximo'],
                'control_inventario' => $request->has('control_inventario'),
                'activo' => $request->has('activo'),
            ]);

            Log::info('Producto creado ID:', ['id' => $producto->id]);

            // ===== GUARDAR IMÁGENES =====
            if ($request->hasFile('imagenes')) {
                Log::info('Procesando imágenes...');
                $folderName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($producto->nombre));
                $folderPath = "empresas/{$empresaId}/productos/{$folderName}_{$producto->id}";
                Log::info('Carpeta destino:', ['path' => $folderPath]);

                foreach ($request->file('imagenes') as $index => $file) {
                    try {
                        $extension = $file->getClientOriginalExtension();
                        $fileName = time() . '_' . uniqid() . '_' . ($index + 1) . '.' . $extension;
                        $path = $file->storeAs($folderPath, $fileName, 'public');
                        Log::info('Imagen guardada:', ['path' => $path]);

                        $imagen = ProductoImagen::create([
                            'producto_id' => $producto->id,
                            'imagen' => $path,
                            'orden' => $index,
                            'principal' => $index === 0,
                        ]);
                        Log::info('Registro de imagen creado ID:', ['id' => $imagen->id]);
                    } catch (\Exception $e) {
                        Log::error('Error al guardar imagen:', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                }
            } else {
                Log::info('No se recibieron archivos de imagen');
            }

            // Sincronizar proveedores
            if (
                (auth()->user()->can('ver_proveedores') || auth()->user()->hasRole('Super Admin'))
                && $request->has('proveedores') && !empty($request->proveedores)
            ) {
                $proveedoresData = [];
                foreach ($request->proveedores as $proveedorId) {
                    if ($proveedorId) {
                        $precioCompra = $request->input("precio_compra.{$proveedorId}", $request->costo_compra);
                        $tiempoEntrega = $request->input("tiempo_entrega.{$proveedorId}", 1);
                        $proveedoresData[$proveedorId] = [
                            'precio_compra' => $precioCompra,
                            'tiempo_entrega_dias' => $tiempoEntrega,
                            'activo' => true,
                        ];
                    }
                }
                if (!empty($proveedoresData)) {
                    $producto->proveedores()->sync($proveedoresData);
                    Log::info('Proveedores sincronizados:', ['count' => count($proveedoresData)]);
                }
            }

            // Sincronizar insumos
            if (
                (auth()->user()->can('ver_insumos') || auth()->user()->hasRole('Super Admin'))
                && $request->has('insumos') && !empty($request->insumos)
            ) {
                $insumosSync = [];
                foreach ($request->insumos as $insumoId => $insumoData) {
                    if (isset($insumoData['activo']) && $insumoData['activo'] == 1 && $insumoId) {
                        $insumosSync[$insumoId] = [
                            'cantidad' => $insumoData['cantidad'] ?? 1,
                            'activo' => true,
                        ];
                    }
                }
                if (!empty($insumosSync)) {
                    $producto->insumos()->sync($insumosSync);
                    Log::info('Insumos sincronizados:', ['count' => count($insumosSync)]);
                }
            }

            // Registrar movimiento inicial
            if ($validated['stock'] > 0) {
                InventarioMovimiento::create([
                    'empresa_id' => $empresaId,
                    'producto_id' => $producto->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'entrada',
                    'motivo' => 'ajuste_inventario',
                    'cantidad' => $validated['stock'],
                    'costo_unitario' => $validated['costo_compra'],
                    'costo_total' => $validated['stock'] * $validated['costo_compra'],
                    'observacion' => 'Stock inicial al crear producto',
                ]);
                Log::info('Movimiento inicial registrado');
            }

            DB::commit();
            Log::info('=== TRANSACCIÓN COMPLETADA EXITOSAMENTE ===');

            return redirect()->route('productos.index')
                ->with('success', 'Producto "' . $producto->nombre . '" creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== ERROR EN STORE PRODUCTO ===');
            Log::error('Mensaje:', ['error' => $e->getMessage()]);
            Log::error('Stack trace:', ['trace' => $e->getTraceAsString()]);

            return back()
                ->withInput()
                ->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalle de producto
     */
    public function show(Producto $producto)
    {
        try {
            $this->verificarEmpresa($producto);

            $producto->load([
                'categoria',
                'proveedores',
                'insumos',
                'movimientos' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                }
            ]);

            return view('productos.show', compact('producto'));

        } catch (\Exception $e) {
            Log::error('Error al mostrar producto: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles del producto.');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(Producto $producto)
    {
        try {
            $this->verificarEmpresa($producto);

            $empresaId = $producto->empresa_id;

            // Obtener todos los productos de la misma empresa (excepto el actual)
            $todosProductos = Producto::where('empresa_id', $empresaId)
                ->where('id', '!=', $producto->id)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'precio_venta']);

            // Para Super Admin, permitir seleccionar empresa
            $empresas = auth()->user()->hasRole('Super Admin')
                ? Empresa::where('activo', true)->orderBy('nombre')->get()
                : collect();

            $categorias = Categoria::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $proveedores = Proveedor::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $insumos = Insumo::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();

            $producto->load(['proveedores', 'insumos']);

            return view('productos.edit', compact('producto', 'categorias', 'proveedores', 'insumos', 'empresas', 'empresaId', 'todosProductos'));

        } catch (\Exception $e) {
            Log::error('Error al cargar edición de producto: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el formulario de edición.');
        }
    }
    public function update(Request $request, Producto $producto)
    {

        $this->verificarEmpresa($producto);

        // Log de inicio
        Log::info('=== INICIO UPDATE PRODUCTO ===', ['producto_id' => $producto->id]);
        Log::info('Datos recibidos:', $request->all());
        Log::info('Archivos recibidos - nuevas_imagenes:', [
            'has_file' => $request->hasFile('nuevas_imagenes'),
            'count' => $request->hasFile('nuevas_imagenes') ? count($request->file('nuevas_imagenes')) : 0
        ]);

        $validated = $request->validate([
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo_barras' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:50|unique:productos,sku,' . $producto->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'required|numeric|min:0',
            'control_inventario' => 'boolean',
            'proveedores' => 'array',
            'insumos' => 'array',
            'imagenes_existentes' => 'nullable|string',
            'nuevas_imagenes' => 'nullable|array',
            'nuevas_imagenes.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        Log::info('Validación pasada');

        DB::beginTransaction();
        try {
            $oldStock = $producto->stock;
            $newStock = $request->has('stock') ? $request->stock : $oldStock;

            // ===== 1. ACTUALIZAR DATOS BÁSICOS =====
            $producto->update([
                'categoria_id' => $validated['categoria_id'] ?? null,
                'codigo_barras' => $validated['codigo_barras'] ?? null,
                'sku' => $validated['sku'] ?? $producto->sku,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'costo_compra' => $validated['costo_compra'],
                'precio_venta' => $validated['precio_venta'],
                'stock' => $newStock,
                'stock_minimo' => $validated['stock_minimo'],
                'stock_maximo' => $validated['stock_maximo'],
                'control_inventario' => $request->has('control_inventario'),
            ]);

            Log::info('Producto actualizado');

            // ===== 2. MANEJAR IMÁGENES =====
            $idsAMantener = [];
            if ($request->filled('imagenes_existentes')) {
                $idsAMantener = explode(',', $request->imagenes_existentes);
                $idsAMantener = array_filter($idsAMantener);
                Log::info('IDs a mantener:', $idsAMantener);
            }

            // Eliminar imágenes que no están en la lista
            $imagenesAEliminar = $producto->imagenes()
                ->when(!empty($idsAMantener), function ($query) use ($idsAMantener) {
                    return $query->whereNotIn('id', $idsAMantener);
                })
                ->get();

            Log::info('Imágenes a eliminar:', ['count' => $imagenesAEliminar->count()]);

            foreach ($imagenesAEliminar as $imagen) {
                Log::info('Eliminando imagen:', ['id' => $imagen->id, 'path' => $imagen->imagen]);

                if (Storage::disk('public')->exists($imagen->imagen)) {
                    Storage::disk('public')->delete($imagen->imagen);
                    Log::info('Archivo eliminado');
                } else {
                    Log::warning('Archivo no encontrado:', ['path' => $imagen->imagen]);
                }
                $imagen->delete();
            }

            // Agregar nuevas imágenes
            if ($request->hasFile('nuevas_imagenes')) {
                $imagenesActuales = $producto->imagenes()->count();
                $nuevasImagenes = count($request->file('nuevas_imagenes'));

                Log::info('Agregando nuevas imágenes:', ['actuales' => $imagenesActuales, 'nuevas' => $nuevasImagenes]);

                if ($imagenesActuales + $nuevasImagenes > 3) {
                    throw new \Exception('No puedes agregar más de 3 imágenes en total.');
                }

                $folderName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($producto->nombre));
                $folderPath = "empresas/{$producto->empresa_id}/productos/{$folderName}_{$producto->id}";

                $ordenActual = $producto->imagenes()->max('orden') ?? -1;
                $ordenActual++;
                $esPrincipal = $producto->imagenes()->count() === 0;

                foreach ($request->file('nuevas_imagenes') as $index => $file) {
                    $extension = $file->getClientOriginalExtension();
                    $fileName = time() . '_' . uniqid() . '_' . ($index + 1) . '.' . $extension;
                    $path = $file->storeAs($folderPath, $fileName, 'public');

                    Log::info('Imagen guardada:', ['path' => $path]);

                    ProductoImagen::create([
                        'producto_id' => $producto->id,
                        'imagen' => $path,
                        'orden' => $ordenActual + $index,
                        'principal' => $esPrincipal && $index === 0,
                    ]);
                }
            }

            // ===== 3. ACTUALIZAR PROVEEDORES =====
            if ($request->has('proveedores') && !empty($request->proveedores)) {
                $proveedoresData = [];
                foreach ($request->proveedores as $proveedorId) {
                    $precioCompra = $request->input("precio_compra.{$proveedorId}", $validated['costo_compra']);
                    $tiempoEntrega = $request->input("tiempo_entrega.{$proveedorId}", 1);
                    $proveedoresData[$proveedorId] = [
                        'precio_compra' => $precioCompra,
                        'tiempo_entrega_dias' => $tiempoEntrega,
                        'activo' => true,
                    ];
                }
                $producto->proveedores()->sync($proveedoresData);
                Log::info('Proveedores sincronizados:', ['count' => count($proveedoresData)]);
            } else {
                $producto->proveedores()->sync([]);
                Log::info('Proveedores eliminados');
            }

            // ===== 4. ACTUALIZAR INSUMOS =====
            if ($request->has('insumos') && !empty($request->insumos)) {
                $insumosSync = [];
                foreach ($request->insumos as $insumoId => $insumoData) {
                    if (isset($insumoData['activo']) && $insumoData['activo'] == 1) {
                        $insumosSync[$insumoId] = [
                            'cantidad' => $insumoData['cantidad'] ?? 1,
                            'activo' => true,
                        ];
                    }
                }
                $producto->insumos()->sync($insumosSync);
                Log::info('Insumos sincronizados:', ['count' => count($insumosSync)]);
            } else {
                $producto->insumos()->sync([]);
                Log::info('Insumos eliminados');
            }

            // ===== 5. REGISTRAR MOVIMIENTO DE STOCK =====
            if ($request->has('stock') && $newStock != $oldStock) {
                $diferencia = $newStock - $oldStock;
                $tipo = $diferencia > 0 ? 'entrada' : 'salida';
                InventarioMovimiento::create([
                    'empresa_id' => $producto->empresa_id,
                    'producto_id' => $producto->id,
                    'user_id' => auth()->id(),
                    'tipo' => $tipo,
                    'motivo' => 'ajuste_inventario',
                    'cantidad' => abs($diferencia),
                    'costo_unitario' => $validated['costo_compra'],
                    'costo_total' => abs($diferencia) * $validated['costo_compra'],
                    'observacion' => 'Ajuste de stock al editar producto',
                ]);
                Log::info('Movimiento de stock registrado', ['diferencia' => $diferencia, 'tipo' => $tipo]);
            }

            // ===== 6. ACTUALIZAR PRODUCTOS RELACIONADOS =====
            if ($request->has('relacionados')) {
                $relacionadosIds = array_filter($request->relacionados);
                if (count($relacionadosIds) > 3) {
                    throw new \Exception('Máximo 3 productos relacionados');
                }
                $producto->syncRelacionados($relacionadosIds);
                Log::info('Productos relacionados actualizados', $relacionadosIds);
            }

            DB::commit();
            Log::info('=== TRANSACCIÓN COMPLETADA EXITOSAMENTE ===');

            return redirect()->route('productos.index')
                ->with('success', 'Producto "' . $producto->nombre . '" actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== ERROR EN UPDATE ===');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar producto (desactivar)
     */
    public function destroy(Producto $producto)
    {
        $this->verificarEmpresa($producto);

        DB::beginTransaction();
        try {
            $nombre = $producto->nombre;

            // Solo desactivar el producto, no eliminar físicamente
            $producto->update(['activo' => false]);

            DB::commit();

            return redirect()->route('productos.index')
                ->with('success', 'Producto "' . $nombre . '" desactivado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desactivar producto: ' . $e->getMessage());
            return back()->with('error', 'Error al desactivar el producto. Intente nuevamente.');
        }
    }

    /**
     * Activar/Desactivar producto
     */
    public function toggleActivo(Producto $producto)
    {
        $this->verificarEmpresa($producto);

        DB::beginTransaction();
        try {
            $producto->update(['activo' => !$producto->activo]);
            DB::commit();

            $estado = $producto->activo ? 'activado' : 'desactivado';
            return back()->with('success', 'Producto "' . $producto->nombre . '" ' . $estado . ' correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado de producto: ' . $e->getMessage());
            return back()->with('error', 'Error al cambiar el estado del producto.');
        }
    }

    /**
     * Buscar productos para autocompletado
     */
    public function search(Request $request)
    {
        try {
            $empresaId = $this->empresaActivaId();
            $search = $request->get('q');

            $query = Producto::where('activo', true)
                ->where(function ($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                        ->orWhere('codigo_barras', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%");
                });

            if (!auth()->user()->hasRole('Super Admin')) {
                $query->where('empresa_id', $empresaId);
            } elseif ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }

            $productos = $query->limit(10)
                ->get(['id', 'nombre', 'codigo_barras', 'precio_venta', 'stock']);

            return response()->json($productos);

        } catch (\Exception $e) {
            Log::error('Error al buscar productos: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
    /**
     * Subir imagen al producto
     */
    public function subirImagen(Request $request, Producto $producto)
    {
        $this->verificarEmpresa($producto);

        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if (!$producto->canAddImagen()) {
            return response()->json([
                'success' => false,
                'message' => 'Máximo 3 imágenes por producto'
            ], 400);
        }

        try {
            // Crear carpeta para el producto
            $folderName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($producto->nombre));
            $folderPath = "empresas/{$producto->empresa_id}/productos/{$folderName}_{$producto->id}";

            $file = $request->file('imagen');
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs($folderPath, $fileName, 'public');

            $orden = $producto->imagenes()->max('orden') + 1;
            $esPrincipal = $producto->imagenes()->count() === 0;

            $imagen = ProductoImagen::create([
                'producto_id' => $producto->id,
                'imagen' => $path,
                'orden' => $orden,
                'principal' => $esPrincipal,
            ]);

            return response()->json([
                'success' => true,
                'imagen' => [
                    'id' => $imagen->id,
                    'url' => $imagen->url,
                    'principal' => $imagen->principal,
                    'orden' => $imagen->orden,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al subir imagen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen'
            ], 500);
        }
    }

    /**
     * Eliminar imagen del producto
     */
    public function eliminarImagen(Producto $producto, ProductoImagen $imagen)
    {
        $this->verificarEmpresa($producto);

        if ($imagen->producto_id !== $producto->id) {
            return response()->json([
                'success' => false,
                'message' => 'La imagen no pertenece a este producto'
            ], 403);
        }

        try {
            $imagen->delete();

            // Reordenar imágenes restantes
            $restantes = $producto->imagenes()->orderBy('orden')->get();
            foreach ($restantes as $index => $img) {
                $img->update(['orden' => $index]);
            }

            // Si se eliminó la principal, establecer una nueva
            if (!$producto->imagenes()->where('principal', true)->exists()) {
                $primera = $producto->imagenes()->first();
                if ($primera) {
                    $primera->update(['principal' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar imagen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen'
            ], 500);
        }
    }

    /**
     * Establecer imagen como principal
     */
    public function imagenPrincipal(Producto $producto, ProductoImagen $imagen)
    {
        $this->verificarEmpresa($producto);

        if ($imagen->producto_id !== $producto->id) {
            return response()->json(['success' => false, 'message' => 'La imagen no pertenece a este producto'], 403);
        }

        try {
            // Quitar principal de todas las imágenes
            $producto->imagenes()->update(['principal' => false]);

            // Establecer nueva principal
            $imagen->update(['principal' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Imagen principal actualizada'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al establecer imagen principal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer la imagen principal'
            ], 500);
        }
    }

    /**
     * Agregar producto relacionado
     */
    public function agregarRelacionado(Request $request, Producto $producto)
    {
        $this->verificarEmpresa($producto);

        $request->validate([
            'producto_relacionado_id' => 'required|exists:productos,id|different:producto_id',
        ]);

        if (!$producto->canAddRelacionado()) {
            return response()->json([
                'success' => false,
                'message' => 'Máximo 3 productos relacionados'
            ], 400);
        }

        $relacionadoId = $request->producto_relacionado_id;

        // Verificar que el producto relacionado pertenece a la misma empresa
        $relacionado = Producto::find($relacionadoId);
        if ($relacionado->empresa_id !== $producto->empresa_id) {
            return response()->json([
                'success' => false,
                'message' => 'El producto debe pertenecer a la misma empresa'
            ], 400);
        }

        // Verificar que no exista ya la relación
        if ($producto->relacionados()->where('producto_relacionado_id', $relacionadoId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto ya está relacionado'
            ], 400);
        }

        try {
            $orden = $producto->relacionados()->count();
            $producto->relacionados()->attach($relacionadoId, ['orden' => $orden, 'activo' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Producto relacionado agregado',
                'relacionado' => [
                    'id' => $relacionado->id,
                    'nombre' => $relacionado->nombre,
                    'precio' => $relacionado->precio_venta,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al agregar producto relacionado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el producto relacionado'
            ], 500);
        }
    }

    /**
     * Eliminar producto relacionado
     */
    public function eliminarRelacionado(Producto $producto, $relacionadoId)
    {
        $this->verificarEmpresa($producto);

        try {
            $producto->relacionados()->detach($relacionadoId);

            // Reordenar los restantes
            $restantes = $producto->relacionados()->get();
            foreach ($restantes as $index => $rel) {
                $producto->relacionados()->updateExistingPivot($rel->id, ['orden' => $index]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto relacionado eliminado'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar producto relacionado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto relacionado'
            ], 500);
        }
    }
    /**
     * Generar SKU único para el producto
     */
    public function generarSkuUnico(Request $request)
    {
        try {
            $sku = Producto::generarSKU();
            return response()->json([
                'success' => true,
                'sku' => $sku
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar SKU: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar SKU único basado en el nombre del producto
     */
    public function generarSkuPorNombre(Request $request)
    {
        try {
            $nombre = $request->input('nombre');
            $base = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $nombre), 0, 6));

            do {
                $numero = mt_rand(1, 99999);
                $sku = $base . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
            } while (Producto::where('sku', $sku)->exists());

            return response()->json([
                'success' => true,
                'sku' => $sku
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar SKU: ' . $e->getMessage()
            ], 500);
        }
    }
}