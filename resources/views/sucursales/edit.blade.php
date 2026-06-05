@extends('layouts.app')

@section('title', 'Editar Sucursal')
@section('page-title', 'Editar: ' . $sucursal->nombre)

@section('content')

<div class="max-w-2xl mx-auto">

    <x-alert type="error" :message="session('error')" />

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <h4 class="text-red-700 font-semibold mb-2">⚠️ Corrige los siguientes errores:</h4>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">📍</div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $sucursal->nombre }}</h2>
            <p class="text-gray-500 mt-2">{{ $sucursal->empresa->nombre ?? '' }}</p>
        </div>

        <form action="{{ route('sucursales.update', $sucursal) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $sucursal->nombre) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $sucursal->direccion) }}"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $sucursal->telefono) }}"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-4 rounded-xl">
                        <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ $sucursal->activo ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Sucursal activa</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-between mt-8 pt-6 border-t">
                @if($sucursal->usuarios()->count() == 0)
                <form action="{{ route('sucursales.destroy', $sucursal) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar esta sucursal?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-6 py-3 border-2 border-red-300 text-red-600 rounded-xl hover:bg-red-50 transition font-medium">🗑️ Eliminar</button>
                </form>
                @else
                <div></div>
                @endif
                <div class="flex gap-4">
                    <a href="{{ route('empresas.show', $sucursal->empresa_id) }}"
                        class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">Cancelar</a>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg flex items-center gap-2">
                        💾 Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection