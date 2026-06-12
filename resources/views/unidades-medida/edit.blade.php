@extends('layouts.app')

@section('title', 'Editar Unidad de Medida')
@section('page-title', 'Editar: ' . $unidad->nombre)

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
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $unidad->nombre }}</h2>
            <p class="mt-2 text-gray-500">Clave: {{ $unidad->clave }}</p>
        </div>

        <form action="{{ route('unidades-medida.update', $unidad) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Tipo *</label>
                    <select name="tipo" required class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('tipo') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <option value="">Seleccionar tipo...</option>
                        @foreach(['Múltiplos / Fracciones / Decimales','Unidades de venta','Unidades específicas de la industria','Mecánica','Tiempo y Espacio','Unidades de empaque','Diversos','Números enteros / Números / Ratios'] as $tipo)
                            <option value="{{ $tipo }}" {{ old('tipo', $unidad->tipo) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                        @endforeach
                    </select>
                    @error('tipo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Clave (no editable)</label>
                    <input type="text" value="{{ $unidad->clave }}" readonly class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-500">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $unidad->nombre) }}" required class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Símbolo</label>
                    <input type="text" name="simbolo" value="{{ old('simbolo', $unidad->simbolo) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="descripcion" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('descripcion', $unidad->descripcion) }}</textarea>
                </div>
                {{-- ✅ CHECKBOX con hidden para enviar 0 cuando desmarcado --}}
                <div>
                    <input type="hidden" name="activo" value="0">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('activo', $unidad->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Activo</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('unidades-medida.index') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                @can('editar_unidades_medida')
                <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">💾 Guardar cambios</button>
                @endcan
            </div>
        </form>
    </div>
</div>
@endsection