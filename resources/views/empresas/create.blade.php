@extends('layouts.app')

@section('title', 'Nueva Empresa')
@section('page-title', 'Nueva Empresa')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('empresas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Empresas
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Nueva</span>
    </li>
@endsection

@section('content')
<div class="max-w-3xl mx-auto" x-data="logoEditor()" x-init="initEditor">
    <x-alert type="error" :message="session('error')" />

    @if($errors->any())
    <div class="p-4 mb-6 border border-red-200 rounded-xl bg-red-50">
        <h4 class="flex items-center gap-2 mb-2 font-semibold text-red-700">⚠️ Corrige los siguientes errores:</h4>
        <ul class="space-y-1 text-sm text-red-600 list-disc list-inside">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">+</div>
            <h2 class="text-2xl font-bold text-slate-800">Registrar nueva empresa</h2>
            <p class="mt-2 text-gray-500">Completa los datos de la empresa</p>
        </div>

        <form action="{{ route('empresas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-5">
                {{-- Logo upload con preview y editor --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Logo de la empresa</label>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div class="relative">
                            <div class="relative w-32 h-32 overflow-hidden bg-gray-100 border-2 border-gray-300 rounded-xl">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" class="object-cover w-full h-full" alt="Preview">
                                </template>
                                <template x-if="!previewUrl">
                                    <div class="flex items-center justify-center w-full h-full">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </template>
                            </div>
                            <button type="button" x-show="previewUrl" @click="openCropper" 
                                    class="absolute bottom-0 right-0 p-1.5 bg-indigo-600 rounded-full shadow-lg hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="flex-1">
                            <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" 
                                   class="hidden" @change="handleFileUpload">
                            <button type="button" @click="document.getElementById('logo').click()" 
                                    class="px-4 py-2 text-sm font-medium text-indigo-600 transition border border-indigo-600 rounded-xl hover:bg-indigo-50">
                                📁 Seleccionar logo
                            </button>
                            <p class="mt-1 text-xs text-gray-500">Formatos: JPG, PNG, GIF, WEBP. Tamaño máximo: 2MB</p>
                        </div>
                    </div>
                    @error('logo') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">RFC *</label>
                    <input type="text" name="rfc" value="{{ old('rfc') }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('rfc') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('rfc') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Licencia *</label>
                    <select name="licencia_id" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('licencia_id') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <option value="">Seleccionar licencia...</option>
                        @foreach($licencias as $licencia)
                            <option value="{{ $licencia->id }}" {{ old('licencia_id') == $licencia->id ? 'selected' : '' }}>
                                {{ $licencia->nombre }} - ${{ number_format($licencia->precio, 2) }} ({{ $licencia->max_usuarios }} usuarios, {{ $licencia->max_sucursales }} sucursales)
                            </option>
                        @endforeach
                    </select>
                    @error('licencia_id') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Correo</label>
                        <input type="email" name="correo" value="{{ old('correo') }}"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('correo') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('correo') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Fecha inicio *</label>
                        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d')) }}" required
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('fecha_inicio') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('fecha_inicio') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Fecha fin *</label>
                        <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" required
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('fecha_fin') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('fecha_fin') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('empresas.index') }}"
                    class="px-6 py-3 font-medium transition border-2 rounded-xl border-slate-300 text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit"
                    class="flex items-center gap-2 px-8 py-3 font-semibold text-white transition shadow-lg rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-500 hover:from-indigo-700 hover:to-cyan-600">
                    💾 Crear empresa
                </button>
            </div>
        </form>
    </div>

    {{-- Modal para editar la imagen --}}
    <div x-show="showCropper" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.away="closeCropper">
        <div class="w-full max-w-2xl p-6 mx-4 bg-white rounded-2xl">
            <h3 class="mb-4 text-xl font-bold">Ajustar logo</h3>
            <div class="relative mb-4">
                <img :src="cropImageUrl" id="crop-image" class="max-w-full mx-auto max-h-96">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" @click="closeCropper"
                        class="px-4 py-2 border rounded-xl hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" @click="cropImage"
                        class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">
                    Aplicar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function logoEditor() {
    return {
        previewUrl: null,
        showCropper: false,
        cropImageUrl: null,
        currentFile: null,
        cropper: null,
        
        initEditor() {
            console.log('initEditor llamado - Create empresa');
        },
        
        handleFileUpload(event) {
            console.log('handleFileUpload llamado');
            const file = event.target.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Formato no válido. Use JPG, PNG, GIF o WEBP');
                    return;
                }
                
                if (file.size > 2 * 1024 * 1024) {
                    alert('El archivo no debe pesar más de 2MB');
                    return;
                }
                
                this.currentFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.cropImageUrl = e.target.result;
                    this.$nextTick(() => {
                        this.openCropper();
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        
        openCropper() {
            console.log('openCropper llamado');
            this.showCropper = true;
            this.$nextTick(() => {
                const image = document.getElementById('crop-image');
                console.log('Imagen encontrada:', image);
                if (image && window.Cropper) {
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    this.cropper = new window.Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        cropBoxResizable: true,
                        cropBoxMovable: true,
                        background: false,
                        autoCropArea: 1,
                    });
                    console.log('Cropper inicializado');
                } else {
                    console.error('Cropper no disponible o imagen no encontrada');
                }
            });
        },
        
        cropImage() {
            console.log('cropImage llamado');
            if (this.cropper) {
                const canvas = this.cropper.getCroppedCanvas({
                    width: 200,
                    height: 200,
                });
                this.previewUrl = canvas.toDataURL();
                
                canvas.toBlob((blob) => {
                    this.currentFile = new File([blob], 'logo.png', { type: 'image/png' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(this.currentFile);
                    document.getElementById('logo').files = dataTransfer.files;
                }, 'image/png');
                
                this.closeCropper();
            }
        },
        
        closeCropper() {
            console.log('closeCropper llamado');
            this.showCropper = false;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        }
    }
}
</script>
@endsection