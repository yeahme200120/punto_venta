{{-- resources/views/formas_pago/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Forma de Pago')
@section('page-title', 'Editar Forma de Pago')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><a href="{{ route('formas_pago.index') }}" class="text-gray-500 hover:text-indigo-600">Formas de Pago</a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Editar</span></li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-cyan-500">
            <h2 class="text-xl font-bold text-white">Editar forma de pago</h2>
            <p class="text-sm text-indigo-100">Modificando: {{ $formaPago->nombre }}</p>
        </div>

        <form action="{{ route('formas_pago.update', $formaPago) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Clave *</label>
                    <input type="text" name="clave" value="{{ old('clave', $formaPago->clave) }}" required
                           class="w-full px-4 py-3 font-mono border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('clave') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $formaPago->nombre) }}" required
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Icono (emoji)</label>
                    <input type="text" name="icono" value="{{ old('icono', $formaPago->icono) }}" maxlength="10"
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Ej: 💵, 💳, 🏦, 📱</p>
                    @error('icono') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Orden global</label>
                    <input type="number" name="orden" value="{{ old('orden', $formaPago->orden) }}" min="0"
                           class="w-32 px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('orden') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 p-4 bg-gray-50 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="activo_global" {{ old('activo_global', $formaPago->activo_global) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Activar globalmente</span>
                        <span class="text-xs text-gray-400">(Al desactivar, se ocultará para todas las empresas)</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="requiere_referencia" {{ old('requiere_referencia', $formaPago->requiere_referencia) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Requiere número de referencia</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="requiere_autorizacion" {{ old('requiere_autorizacion', $formaPago->requiere_autorizacion) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Requiere autorización especial</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t">
                <a href="{{ route('formas_pago.index') }}" class="px-6 py-3 transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                    Cancelar
                </a>
                <button type="submit" class="px-8 py-3 text-white transition shadow-md bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                    💾 Actualizar forma de pago
                </button>
            </div>
        </form>
    </div>

    {{-- Advertencia sobre efectos globales --}}
    <div class="p-4 mt-4 text-sm text-yellow-700 bg-yellow-50 rounded-xl">
        <p class="font-semibold">⚠️ Importante:</p>
        <p>Los cambios en el catálogo global afectan a <strong>todas las empresas</strong>. Si desactivas una forma de pago, dejará de estar disponible para todas las empresas que la tengan configurada.</p>
    </div>
</div>
@endsection