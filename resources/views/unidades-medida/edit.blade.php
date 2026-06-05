{{-- resources/views/unidades-medida/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Unidad de Medida')
@section('page-title', 'Editar: ' . $unidad_medida->nombre) {{-- Cambiado a $unidad_medida --}}
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('unidades-medida.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Unidades de Medida
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('unidades-medida.edit', $unidad_medida) }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            {{ $unidad_medida->nombre }}
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Editar</span>
    </li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
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
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">📏</div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $unidad_medida->nombre }}</h2> {{-- Cambiado a $unidad_medida --}}
            <p class="mt-2 text-gray-500">Clave: {{ $unidad_medida->clave }}</p> {{-- Cambiado a $unidad_medida --}}
        </div>

        <form action="{{ route('unidades-medida.update', $unidad_medida) }}" method="POST"> {{-- Cambiado a $unidad_medida --}}
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Tipo *</label>
                    <select name="tipo" required class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('tipo') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <option value="">Seleccionar tipo...</option>
                        <option value="Múltiplos / Fracciones / Decimales" {{ old('tipo', $unidad_medida->tipo) == 'Múltiplos / Fracciones / Decimales' ? 'selected' : '' }}>Múltiplos / Fracciones / Decimales</option>
                        <option value="Unidades de venta" {{ old('tipo', $unidad_medida->tipo) == 'Unidades de venta' ? 'selected' : '' }}>Unidades de venta</option>
                        <option value="Unidades específicas de la industria" {{ old('tipo', $unidad_medida->tipo) == 'Unidades específicas de la industria' ? 'selected' : '' }}>Unidades específicas de la industria</option>
                        <option value="Mecánica" {{ old('tipo', $unidad_medida->tipo) == 'Mecánica' ? 'selected' : '' }}>Mecánica</option>
                        <option value="Tiempo y Espacio" {{ old('tipo', $unidad_medida->tipo) == 'Tiempo y Espacio' ? 'selected' : '' }}>Tiempo y Espacio</option>
                        <option value="Unidades de empaque" {{ old('tipo', $unidad_medida->tipo) == 'Unidades de empaque' ? 'selected' : '' }}>Unidades de empaque</option>
                        <option value="Diversos" {{ old('tipo', $unidad_medida->tipo) == 'Diversos' ? 'selected' : '' }}>Diversos</option>
                        <option value="Números enteros" {{ old('tipo', $unidad_medida->tipo) == 'Números enteros' ? 'selected' : '' }}>Números enteros / Números / Ratios</option>
                    </select>
                    @error('tipo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Clave *</label>
                    <input type="text" name="clave" value="{{ old('clave', $unidad_medida->clave) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('clave') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    <p class="mt-1 text-xs text-gray-400">La clave debe ser única y no puede repetirse</p>
                    @error('clave') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $unidad_medida->nombre) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Símbolo</label>
                    <input type="text" name="simbolo" value="{{ old('simbolo', $unidad_medida->simbolo) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('simbolo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="descripcion" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('descripcion', $unidad_medida->descripcion) }}</textarea>
                    @error('descripcion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('activo', $unidad_medida->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Activo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('unidades-medida.index') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">💾 Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection