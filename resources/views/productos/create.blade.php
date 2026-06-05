@extends('layouts.app')

@section('title', 'Nuevo Producto')
@section('page-title', 'Nuevo Producto')
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
        <span class="font-medium text-gray-700">Nuevo</span>
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

    {{-- Si no hay empresa seleccionada, mostrar selector --}}
    @if(!$empresaId && auth()->user()->hasRole('Super Admin'))
    <div class="p-8 mb-6 text-center bg-yellow-50 rounded-3xl">
        <div class="mb-3 text-4xl">🏢</div>
        <h3 class="mb-2 text-xl font-bold text-yellow-800">Selecciona una empresa</h3>
        <p class="mb-4 text-yellow-700">Para registrar un producto, primero debes seleccionar una empresa activa.</p>
        <div class="flex justify-center">
            <div class="relative inline-block">
                <select id="select-empresa" class="px-4 py-2 bg-white border border-yellow-300 rounded-xl focus:ring-2 focus:ring-yellow-500">
                    <option value="">-- Seleccionar empresa --</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                    @endforeach
                </select>
                <button onclick="cambiarEmpresa()" class="px-4 py-2 ml-3 text-white transition bg-yellow-600 rounded-xl hover:bg-yellow-700">
                    Continuar
                </button>
            </div>
        </div>
    </div>
    @elseif(!$empresaId && !auth()->user()->hasRole('Super Admin'))
    <div class="p-8 mb-6 text-center bg-red-50 rounded-3xl">
        <div class="mb-3 text-4xl">⚠️</div>
        <h3 class="mb-2 text-xl font-bold text-red-800">No hay empresa seleccionada</h3>
        <p class="text-red-700">Contacta al administrador para asignarte una empresa.</p>
    </div>
    @else
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">📦</div>
            <h2 class="text-2xl font-bold text-slate-800">Registrar producto</h2>
            @if($empresaId)
            <p class="mt-1 text-sm text-gray-500">Empresa: <span class="font-medium text-indigo-600">{{ $empresas->firstWhere('id', $empresaId)->nombre ?? 'Seleccionada' }}</span></p>
            @endif
        </div>

        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" id="productoForm">
            @csrf
            <div class="space-y-5">
                {{-- Imágenes del producto --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">📸 Imágenes del producto (máx 3)</label>
                    <div class="grid grid-cols-3 gap-3 mb-3" id="imagenes-preview">
                        <!-- Las imágenes se agregarán aquí dinámicamente -->
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" id="btn-agregar-imagen" class="px-4 py-2 text-sm font-medium text-indigo-600 transition border border-indigo-600 rounded-xl hover:bg-indigo-50">
                            📁 Seleccionar imágenes
                        </button>
                        <p class="text-xs text-gray-500">Formatos: JPG, PNG, GIF, WEBP. Máx 2MB c/u, máximo 3 imágenes</p>
                    </div>
                    <input type="file" name="imagenes[]" id="imagen_input" accept="image/*" multiple class="hidden">
                    @error('imagenes') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Códigos automáticos --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Código de Barras</label>
                        <div class="code-preview" id="codigo-barras-preview">{{ old('codigo_barras', $codigoBarras) }}</div>
                        <input type="hidden" name="codigo_barras" value="{{ old('codigo_barras', $codigoBarras) }}">
                        <button type="button" onclick="regenerarCodigoBarras()" class="mt-1 text-xs text-indigo-600 hover:text-indigo-800">Regenerar</button>
                        @error('codigo_barras') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">SKU *</label>
                        <div class="code-preview" id="sku-preview">{{ old('sku', $sku) }}</div>
                        <input type="hidden" name="sku" value="{{ old('sku', $sku) }}">
                        <button type="button" onclick="regenerarSKU()" class="mt-1 text-xs text-indigo-600 hover:text-indigo-800">Regenerar</button>
                        @error('sku') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'error-border' : 'border-gray-300' }}">
                    @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Categoría</label>
                    <select name="categoria_id" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('categoria_id') ? 'error-border' : 'border-gray-300' }}">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Costo Compra $ *</label>
                        <input type="number" name="costo_compra" value="{{ old('costo_compra', 0) }}" required min="0" step="0.01"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('costo_compra') ? 'error-border' : 'border-gray-300' }}">
                        @error('costo_compra') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Precio Venta $ *</label>
                        <input type="number" name="precio_venta" value="{{ old('precio_venta', 0) }}" required min="0" step="0.01"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('precio_venta') ? 'error-border' : 'border-gray-300' }}">
                        @error('precio_venta') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Stock *</label>
                        <input type="number" name="stock" value="{{ old('stock', 0) }}" required min="0"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('stock') ? 'error-border' : 'border-gray-300' }}">
                        @error('stock') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Stock Mínimo *</label>
                        <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 5) }}" required min="0"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('stock_minimo') ? 'error-border' : 'border-gray-300' }}">
                        @error('stock_minimo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Stock Máximo *</label>
                        <input type="number" name="stock_maximo" value="{{ old('stock_maximo', 100) }}" required min="0"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $errors->has('stock_maximo') ? 'error-border' : 'border-gray-300' }}">
                        @error('stock_maximo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="control_inventario" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('control_inventario', true) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Controlar inventario</span>
                    </label>
                </div>

                {{-- PRODUCTOS RELACIONADOS --}}
                @if($productosRelacionados && $productosRelacionados->count() > 0)
                <div class="pt-5 border-t">
                    <h3 class="mb-3 text-lg font-bold text-slate-800">🔗 Productos relacionados (máx 3)</h3>
                    <div id="relacionados-container" class="space-y-2">
                        <div class="flex items-center gap-2">
                            <select name="relacionados[]" class="flex-1 px-3 py-2 text-sm border rounded-lg">
                                <option value="">Seleccionar producto relacionado...</option>
                                @foreach($productosRelacionados as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->nombre }} - ${{ number_format($prod->precio_venta, 2) }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="agregarRelacionado()" class="px-3 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">+</button>
                        </div>
                    </div>
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
                            <div class="flex items-center gap-3 p-3 border rounded-xl hover:bg-slate-50">
                                <input type="checkbox" name="insumos[{{ $insumo->id }}][activo]" value="1" 
                                    {{ old('insumos.' . $insumo->id . '.activo') ? 'checked' : '' }}
                                    class="w-4 h-4 text-indigo-600 rounded insumo-check">
                                <span class="flex-1 text-sm">{{ $insumo->nombre }}</span>
                                <span class="text-xs text-gray-400">{{ $insumo->unidad_medida }}</span>
                                <input type="number" name="insumos[{{ $insumo->id }}][cantidad]" value="{{ old('insumos.' . $insumo->id . '.cantidad', 1) }}" min="0.01" step="0.01" 
                                    class="w-20 px-2 py-1 text-sm text-center border border-gray-300 rounded-lg"
                                    {{ old('insumos.' . $insumo->id . '.activo') ? '' : 'disabled' }}>
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
                            <div class="flex items-center gap-3 p-3 border rounded-xl hover:bg-slate-50">
                                <input type="checkbox" name="proveedores[]" value="{{ $prov->id }}" 
                                    {{ in_array($prov->id, old('proveedores', [])) ? 'checked' : '' }}
                                    class="w-4 h-4 text-indigo-600 rounded prov-check">
                                <span class="flex-1 text-sm">{{ $prov->nombre }}</span>
                                <input type="number" name="precio_compra[{{ $prov->id }}]" placeholder="Precio" min="0" step="0.01" 
                                    value="{{ old('precio_compra.' . $prov->id, 0) }}"
                                    class="px-2 py-1 text-sm border border-gray-300 rounded-lg w-28" 
                                    {{ in_array($prov->id, old('proveedores', [])) ? '' : 'disabled' }}>
                                <input type="number" name="tiempo_entrega[{{ $prov->id }}]" placeholder="Días" min="1" 
                                    value="{{ old('tiempo_entrega.' . $prov->id, 1) }}"
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 rounded-lg" 
                                    {{ in_array($prov->id, old('proveedores', [])) ? '' : 'disabled' }}>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('productos.index') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">💾 Crear producto</button>
            </div>
        </form>
    </div>
    @endif

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
let nuevasImagenes = [];
let previewUrlsNuevas = [];
let relacionadosCount = 1;

function actualizarInputFile() {
    const dataTransfer = new DataTransfer();
    nuevasImagenes.forEach(file => dataTransfer.items.add(file));
    document.getElementById('imagen_input').files = dataTransfer.files;
}

document.addEventListener('DOMContentLoaded', function() {
    // Botón para agregar imágenes
    const btnAgregar = document.getElementById('btn-agregar-imagen');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            document.getElementById('imagen_input').click();
        });
    }

    // Input file para imágenes
    const imagenInput = document.getElementById('imagen_input');
    if (imagenInput) {
        imagenInput.addEventListener('change', handleFileUpload);
    }

    // Eventos de checkboxes
    document.querySelectorAll('.insumo-check').forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.flex');
            const cantidadInput = row.querySelector('input[type="number"]');
            if (cantidadInput) {
                cantidadInput.disabled = !this.checked;
            }
        });
    });

    document.querySelectorAll('.prov-check').forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.flex');
            const inputs = row.querySelectorAll('input[type="number"]');
            inputs.forEach(input => input.disabled = !this.checked);
        });
    });
});

function handleFileUpload(event) {
    const files = Array.from(event.target.files);
    const imagenesExistentes = document.querySelectorAll('#imagenes-preview .image-container').length;
    const totalImages = imagenesExistentes + files.length;

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
            renderPreviews();
            actualizarInputFile();
        };
        reader.readAsDataURL(file);
    });
    event.target.value = '';
}

function renderPreviews() {
    const container = document.getElementById('imagenes-preview');
    if (!container) return;
    
    container.innerHTML = '';

    previewUrlsNuevas.forEach((url, index) => {
        const div = document.createElement('div');
        div.className = 'relative image-container group';
        div.innerHTML = `
            <img src="${url}" class="object-cover w-full h-full">
            <div class="absolute inset-0 flex items-center justify-center gap-2 transition-opacity rounded-lg opacity-0 bg-black/50 group-hover:opacity-100">
                <button type="button" class="p-1 text-white bg-yellow-500 rounded edit-btn hover:bg-yellow-600" data-index="${index}">✂️</button>
                <button type="button" class="p-1 text-white bg-red-500 rounded delete-btn hover:bg-red-600" data-index="${index}">🗑️</button>
            </div>
        `;
        container.appendChild(div);
    });

    // Eventos para editar y eliminar
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            currentFileIndex = index;
            openCropper(previewUrlsNuevas[index]);
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            eliminarImagen(index);
        });
    });
}

function eliminarImagen(index) {
    Swal.fire({
        title: '¿Eliminar imagen?', text: 'Esta acción no se puede deshacer', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            nuevasImagenes.splice(index, 1);
            previewUrlsNuevas.splice(index, 1);
            renderPreviews();
            actualizarInputFile();
        }
    });
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
            const newFile = new File([blob], `imagen_${Date.now()}.png`, { type: 'image/png' });
            const newPreviewUrl = URL.createObjectURL(blob);
            
            if (currentFileIndex !== null) {
                nuevasImagenes[currentFileIndex] = newFile;
                previewUrlsNuevas[currentFileIndex] = newPreviewUrl;
                renderPreviews();
                closeCropper();
                actualizarInputFile();
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

function regenerarSKU() {
    const random = Math.floor(Math.random() * 100000);
    const nuevoSku = `PROD-${random.toString().padStart(5, '0')}`;
    document.querySelector('input[name="sku"]').value = nuevoSku;
    document.getElementById('sku-preview').innerText = nuevoSku;
}

function cambiarEmpresa() {
    const empresaId = document.getElementById('select-empresa').value;
    if (empresaId) {
        window.location.href = `/empresa/${empresaId}/cambiar?return_url=${encodeURIComponent(window.location.pathname)}`;
    }
}

let relacionadosCountVar = 1;

function agregarRelacionado() {
    if (relacionadosCountVar >= 3) {
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
            @foreach($productosRelacionados ?? [] as $prod)
                <option value="{{ $prod->id }}">{{ $prod->nombre }} - ${{ number_format($prod->precio_venta, 2) }}</option>
            @endforeach
        </select>
        <button type="button" onclick="this.parentElement.remove(); relacionadosCountVar--" class="px-3 py-2 text-sm text-white bg-red-500 rounded-lg hover:bg-red-600">-</button>
    `;
    container.appendChild(newDiv);
    relacionadosCountVar++;
}

// Eventos del modal
document.getElementById('close-cropper')?.addEventListener('click', closeCropper);
document.getElementById('apply-crop')?.addEventListener('click', applyCrop);
</script>
@endsection