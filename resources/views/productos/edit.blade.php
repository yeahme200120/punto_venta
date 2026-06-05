@extends('layouts.app')

@section('title', 'Editar Producto')
@section('page-title', 'Editar: ' . $producto->nombre)
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('productos.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Productos
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('productos.show', $producto) }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            {{ $producto->nombre }}
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Editar</span>
    </li>
@endsection

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .cropper-modal { z-index: 10000 !important; }
        .cropper-container { z-index: 10001 !important; }
        .code-preview {
            font-family: monospace;
            background: #f3f4f6;
            padding: 0.5rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            color: #4f46e5;
        }
        .error-border {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
        .image-container {
            position: relative;
            width: 100%;
            height: 96px;
            overflow: hidden;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <x-alert type="error" :message="session('error')" />
    <x-alert type="success" :message="session('success')" />

    @if($errors->any())
    <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
        <h4 class="mb-2 font-semibold text-red-700">⚠️ Corrige los siguientes errores:</h4>
        <ul class="space-y-1 text-sm text-red-600 list-disc list-inside">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">📦</div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $producto->nombre }}</h2>
        </div>

        <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data" id="productoForm">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                {{-- Imágenes del producto --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">📸 Imágenes del producto (máx 3)</label>
                    
                    {{-- Contenedor de imágenes --}}
                    <div class="grid grid-cols-3 gap-3 mb-3" id="imagenes-preview">
                        @foreach($producto->imagenes as $imagen)
                        <div class="relative image-container group" data-imagen-id="{{ $imagen->id }}" data-imagen-url="{{ $imagen->url }}" data-imagen-principal="{{ $imagen->principal ? '1' : '0' }}">
                            <img src="{{ $imagen->url }}" class="object-cover w-full h-full" alt="Imagen del producto">
                            <div class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity rounded-lg opacity-0 bg-black/50 group-hover:opacity-100">
                                <button type="button" class="p-1 text-white bg-yellow-500 rounded edit-btn hover:bg-yellow-600">✂️</button>
                                <button type="button" class="p-1 text-white bg-red-500 rounded delete-btn hover:bg-red-600">🗑️</button>
                            </div>
                            @if($imagen->principal)
                            <span class="absolute px-1 text-xs text-white bg-yellow-500 rounded top-1 right-1">Principal</span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <input type="hidden" name="imagenes_existentes" id="imagenes_existentes" value="{{ $producto->imagenes->pluck('id')->implode(',') }}">

                    <div class="flex items-center gap-3">
                        <input type="file" name="nuevas_imagenes[]" id="imagen_input" accept="image/*" multiple class="hidden">
                        <button type="button" id="btn-agregar-imagen" class="px-4 py-2 text-sm font-medium text-indigo-600 transition border border-indigo-600 rounded-xl hover:bg-indigo-50">
                            📁 Agregar imágenes
                        </button>
                        <p class="text-xs text-gray-500">Formatos: JPG, PNG, GIF, WEBP. Máx 2MB c/u, máximo 3 imágenes en total</p>
                    </div>
                    <div id="nuevas-imagenes-preview" class="grid grid-cols-3 gap-3 mt-3"></div>
                    @error('nuevas_imagenes') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Códigos automáticos --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Código de Barras</label>
                        <div class="code-preview" id="codigo-barras-preview">{{ old('codigo_barras', $producto->codigo_barras) }}</div>
                        <input type="hidden" name="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}">
                        <button type="button" onclick="regenerarCodigoBarras()" class="mt-1 text-xs text-indigo-600 hover:text-indigo-800">Regenerar</button>
                        @error('codigo_barras') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">SKU *</label>
                        <div class="code-preview" id="sku-preview">{{ old('sku', $producto->sku) }}</div>
                        <input type="hidden" name="sku" value="{{ old('sku', $producto->sku) }}">
                        @error('sku') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'error-border' : 'border-gray-300' }}">
                    @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Categoría</label>
                    <select name="categoria_id" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('categoria_id') ? 'error-border' : 'border-gray-300' }}">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    @error('descripcion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Costo Compra $ *</label>
                        <input type="number" name="costo_compra" value="{{ old('costo_compra', $producto->costo_compra) }}" required min="0" step="0.01"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('costo_compra') ? 'error-border' : 'border-gray-300' }}">
                        @error('costo_compra') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Precio Venta $ *</label>
                        <input type="number" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" required min="0" step="0.01"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('precio_venta') ? 'error-border' : 'border-gray-300' }}">
                        @error('precio_venta') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock', $producto->stock) }}" min="0" step="0.01"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('stock') ? 'error-border' : 'border-gray-300' }}">
                        @error('stock') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Stock Mínimo *</label>
                        <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}" required min="0"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('stock_minimo') ? 'error-border' : 'border-gray-300' }}">
                        @error('stock_minimo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Stock Máximo *</label>
                        <input type="number" name="stock_maximo" value="{{ old('stock_maximo', $producto->stock_maximo) }}" required min="0"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('stock_maximo') ? 'error-border' : 'border-gray-300' }}">
                        @error('stock_maximo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="control_inventario" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('control_inventario', $producto->control_inventario) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Controlar inventario</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('activo', $producto->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Producto activo</span>
                    </label>
                </div>

                {{-- PRODUCTOS RELACIONADOS --}}
                @if($todosProductos && $todosProductos->count() > 0)
                <div class="pt-5 border-t">
                    <h3 class="mb-3 text-lg font-bold text-slate-800">🔗 Productos relacionados (máx 3)</h3>
                    <div id="relacionados-container" class="space-y-2">
                        @foreach($producto->relacionados as $relacionado)
                        <div class="flex items-center gap-2">
                            <select name="relacionados[]" class="flex-1 px-3 py-2 text-sm border rounded-lg">
                                <option value="">Seleccionar producto relacionado...</option>
                                @foreach($todosProductos as $prod)
                                    <option value="{{ $prod->id }}" {{ $relacionado->id == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->nombre }} - ${{ number_format($prod->precio_venta, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="px-3 py-2 text-sm text-white bg-red-500 rounded-lg remove-relacionado-btn hover:bg-red-600">-</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="agregarRelacionado()" class="px-3 py-2 mt-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        + Agregar producto relacionado
                    </button>
                    <p class="mt-2 text-xs text-gray-500">Selecciona hasta 3 productos relacionados</p>
                </div>
                @endif

                {{-- INSUMOS --}}
                @if(auth()->user()->can('ver_insumos') || auth()->user()->hasRole('Super Admin'))
                    @if($insumos && $insumos->count() > 0)
                    <div class="pt-5 border-t">
                        <h3 class="mb-3 text-lg font-bold text-slate-800">🧱 Insumos del producto</h3>
                        <div class="space-y-3 overflow-y-auto max-h-60">
                            @foreach($insumos as $insumo)
                            @php $pivot = $producto->insumos->find($insumo->id); @endphp
                            <div class="flex items-center gap-3 p-3 border rounded-xl hover:bg-slate-50">
                                <input type="checkbox" name="insumos[{{ $insumo->id }}][activo]" value="1" {{ $pivot ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded insumo-check">
                                <span class="flex-1 text-sm">{{ $insumo->nombre }}</span>
                                <span class="text-xs text-gray-400">{{ $insumo->unidad_medida }}</span>
                                <input type="number" name="insumos[{{ $insumo->id }}][cantidad]" value="{{ $pivot->pivot->cantidad ?? 1 }}" min="0.01" step="0.01" 
                                    class="w-20 px-2 py-1 text-sm text-center border border-gray-300 rounded-lg" {{ $pivot ? '' : 'disabled' }}>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif

                {{-- PROVEEDORES --}}
                @if(auth()->user()->can('ver_proveedores') || auth()->user()->hasRole('Super Admin'))
                    @if($proveedores && $proveedores->count() > 0)
                    <div class="pt-5 border-t">
                        <h3 class="mb-3 text-lg font-bold text-slate-800">🚚 Proveedores</h3>
                        <div class="space-y-3 overflow-y-auto max-h-60">
                            @foreach($proveedores as $prov)
                            @php $piv = $producto->proveedores->find($prov->id); @endphp
                            <div class="flex items-center gap-3 p-3 border rounded-xl hover:bg-slate-50">
                                <input type="checkbox" name="proveedores[]" value="{{ $prov->id }}" {{ $piv ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded prov-check">
                                <span class="flex-1 text-sm">{{ $prov->nombre }}</span>
                                <input type="number" name="precio_compra[{{ $prov->id }}]" placeholder="Precio" min="0" step="0.01" value="{{ $piv->pivot->precio_compra ?? 0 }}"
                                    class="px-2 py-1 text-sm border border-gray-300 rounded-lg w-28" {{ $piv ? '' : 'disabled' }}>
                                <input type="number" name="tiempo_entrega[{{ $prov->id }}]" placeholder="Días" min="1" value="{{ $piv->pivot->tiempo_entrega_dias ?? 1 }}"
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 rounded-lg" {{ $piv ? '' : 'disabled' }}>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            <div class="flex justify-between pt-6 mt-8 border-t">
                <div class="flex gap-4">
                    <button type="button" onclick="toggleActivo({{ $producto->id }})" 
                            class="px-6 py-3 border-2 {{ $producto->activo ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-green-300 text-green-600 hover:bg-green-50' }} rounded-xl transition font-medium">
                        {{ $producto->activo ? '🔴 Desactivar' : '🟢 Activar' }}
                    </button>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('productos.index') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">💾 Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Modal para Cropper --}}
    <div id="cropper-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-black/70">
        <div class="w-full max-w-2xl p-6 mx-4 bg-white rounded-2xl">
            <h3 class="mb-4 text-xl font-bold">Ajustar imagen</h3>
            <div class="relative mb-4">
                <img id="crop-image" class="max-w-full mx-auto max-h-96">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="close-cropper" class="px-4 py-2 border rounded-xl hover:bg-gray-50">Cancelar</button>
                <button type="button" id="apply-crop" class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Aplicar cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
let cropper = null;
let currentFileIndex = null;
let isExistingImage = false;
let existingImageId = null;
let nuevasImagenes = [];
let previewUrlsNuevas = [];
let relacionadosCount = {{ $producto->relacionados->count() }};

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar campo de imágenes existentes
    actualizarImagenesExistentes();
    
    // Botón para agregar imágenes
    const btnAgregar = document.getElementById('btn-agregar-imagen');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            document.getElementById('imagen_input').click();
        });
    }

    // Input file para nuevas imágenes
    const imagenInput = document.getElementById('imagen_input');
    if (imagenInput) {
        imagenInput.addEventListener('change', handleFileUpload);
    }

    // Eventos para imágenes existentes
    document.querySelectorAll('#imagenes-preview .edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = this.closest('.image-container');
            const imagenUrl = container.dataset.imagenUrl;
            existingImageId = container.dataset.imagenId;
            isExistingImage = true;
            openCropper(imagenUrl);
        });
    });

    document.querySelectorAll('#imagenes-preview .delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = this.closest('.image-container');
            const imagenId = container.dataset.imagenId;
            eliminarImagenExistente(imagenId);
        });
    });

    // Eventos de checkboxes
    document.querySelectorAll('.insumo-check').forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.flex');
            const cantidadInput = row.querySelector('input[type="number"]');
            if (cantidadInput) cantidadInput.disabled = !this.checked;
        });
    });

    document.querySelectorAll('.prov-check').forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.flex');
            const inputs = row.querySelectorAll('input[type="number"]');
            inputs.forEach(input => input.disabled = !this.checked);
        });
    });

    // Eliminar productos relacionados
    document.querySelectorAll('.remove-relacionado-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.flex').remove();
            relacionadosCount--;
        });
    });
});

function handleFileUpload(event) {
    const files = Array.from(event.target.files);
    const imagenesExistentes = document.querySelectorAll('#imagenes-preview .image-container').length;
    const totalImages = imagenesExistentes + nuevasImagenes.length + files.length;

    if (totalImages > 3) {
        Swal.fire({ icon: 'warning', title: 'Límite excedido', text: 'Máximo 3 imágenes por producto', confirmButtonColor: '#4f46e5' });
        event.target.value = '';
        return;
    }

    files.forEach((file) => {
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({ icon: 'error', title: 'Archivo muy grande', text: `La imagen ${file.name} excede el límite de 2MB`, confirmButtonColor: '#4f46e5' });
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({ icon: 'error', title: 'Formato no válido', text: `El archivo ${file.name} no es una imagen válida`, confirmButtonColor: '#4f46e5' });
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewUrlsNuevas.push(e.target.result);
            nuevasImagenes.push(file);
            renderPreviewsNuevas();
            actualizarInputFile();
        };
        reader.readAsDataURL(file);
    });
    event.target.value = '';
}

function renderPreviewsNuevas() {
    const container = document.getElementById('nuevas-imagenes-preview');
    if (!container) return;
    
    container.innerHTML = '';

    previewUrlsNuevas.forEach((url, index) => {
        const div = document.createElement('div');
        div.className = 'relative image-container group';
        div.innerHTML = `
            <img src="${url}" class="object-cover w-full h-full">
            <div class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity rounded-lg opacity-0 bg-black/50 group-hover:opacity-100">
                <button type="button" class="p-1 text-white bg-yellow-500 rounded edit-nueva-btn hover:bg-yellow-600" data-index="${index}">✂️</button>
                <button type="button" class="p-1 text-white bg-red-500 rounded delete-nueva-btn hover:bg-red-600" data-index="${index}">🗑️</button>
            </div>
            <span class="absolute px-1 text-xs text-white bg-blue-500 rounded bottom-1 left-1">Nueva</span>
        `;
        container.appendChild(div);
    });

    document.querySelectorAll('.edit-nueva-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            currentFileIndex = index;
            isExistingImage = false;
            openCropper(previewUrlsNuevas[index]);
        });
    });

    document.querySelectorAll('.delete-nueva-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            eliminarNuevaImagen(index);
        });
    });
}

function actualizarInputFile() {
    const dataTransfer = new DataTransfer();
    nuevasImagenes.forEach(file => dataTransfer.items.add(file));
    document.getElementById('imagen_input').files = dataTransfer.files;
}

function eliminarNuevaImagen(index) {
    Swal.fire({
        title: '¿Eliminar imagen?', text: 'Esta acción no se puede deshacer', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            nuevasImagenes.splice(index, 1);
            previewUrlsNuevas.splice(index, 1);
            renderPreviewsNuevas();
            actualizarInputFile();
        }
    });
}

function eliminarImagenExistente(imagenId) {
    Swal.fire({
        title: '¿Eliminar imagen?', text: 'Esta acción no se puede deshacer', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const container = document.querySelector(`#imagenes-preview .image-container[data-imagen-id="${imagenId}"]`);
            if (container) {
                container.remove();
                actualizarImagenesExistentes();
            }
        }
    });
}

function actualizarImagenesExistentes() {
    const containers = document.querySelectorAll('#imagenes-preview .image-container');
    const ids = Array.from(containers).map(container => container.dataset.imagenId).filter(id => id);
    const inputExistentes = document.getElementById('imagenes_existentes');
    if (inputExistentes) {
        inputExistentes.value = ids.join(',');
    }
}

function openCropper(imageUrl) {
    const modal = document.getElementById('cropper-modal');
    const image = document.getElementById('crop-image');
    image.src = imageUrl;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    setTimeout(() => {
        if (cropper) cropper.destroy();
        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            cropBoxResizable: true,
            cropBoxMovable: true,
            background: false,
            autoCropArea: 1,
        });
    }, 100);
}

function closeCropper() {
    const modal = document.getElementById('cropper-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

function applyCrop() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({ width: 200, height: 200 });
        canvas.toBlob((blob) => {
            if (isExistingImage) {
                Swal.fire({ icon: 'success', title: 'Imagen actualizada', text: 'Los cambios se aplicarán al guardar el producto', confirmButtonColor: '#4f46e5' });
                closeCropper();
            } else if (currentFileIndex !== null) {
                const newFile = new File([blob], `imagen_${Date.now()}.png`, { type: 'image/png' });
                const newPreviewUrl = URL.createObjectURL(blob);
                nuevasImagenes[currentFileIndex] = newFile;
                previewUrlsNuevas[currentFileIndex] = newPreviewUrl;
                renderPreviewsNuevas();
                actualizarInputFile();
                closeCropper();
            }
        }, 'image/png');
    }
}

function regenerarCodigoBarras() {
    const random = Math.floor(Math.random() * 10000000000000);
    const nuevoCodigo = random.toString().padStart(13, '0').substring(0, 13);
    document.querySelector('input[name="codigo_barras"]').value = nuevoCodigo;
    document.getElementById('codigo-barras-preview').innerText = nuevoCodigo;
}

function agregarRelacionado() {
    if (relacionadosCount >= 3) {
        Swal.fire({ icon: 'warning', title: 'Límite alcanzado', text: 'Máximo 3 productos relacionados por producto', confirmButtonColor: '#4f46e5' });
        return;
    }
    const container = document.getElementById('relacionados-container');
    if (!container) return;
    const newDiv = document.createElement('div');
    newDiv.className = 'flex items-center gap-2';
    newDiv.innerHTML = `
        <select name="relacionados[]" class="flex-1 px-3 py-2 text-sm border rounded-lg">
            <option value="">Seleccionar producto relacionado...</option>
            @foreach($todosProductos ?? [] as $prod)
                <option value="{{ $prod->id }}">{{ $prod->nombre }} - ${{ number_format($prod->precio_venta, 2) }}</option>
            @endforeach
        </select>
        <button type="button" class="px-3 py-2 text-sm text-white bg-red-500 rounded-lg remove-relacionado-btn hover:bg-red-600">-</button>
    `;
    container.appendChild(newDiv);
    relacionadosCount++;
    
    newDiv.querySelector('.remove-relacionado-btn').addEventListener('click', function() {
        this.parentElement.remove();
        relacionadosCount--;
    });
}

function toggleActivo(productoId) {
    fetch(`/productos/${productoId}/toggle-activo`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    }).then(response => response.json()).then(data => {
        if (data.success) location.reload();
        else Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cambiar estado', confirmButtonColor: '#4f46e5' });
    });
}

document.getElementById('close-cropper')?.addEventListener('click', closeCropper);
document.getElementById('apply-crop')?.addEventListener('click', applyCrop);
</script>
@endsection