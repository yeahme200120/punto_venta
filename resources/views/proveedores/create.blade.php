@extends('layouts.app')

@section('title', 'Editar Proveedor')
@section('page-title', 'Editar: ' . $proveedor->nombre)
{{-- En create.blade.php --}}
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('proveedores.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Proveedores
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Nuevo</span>
    </li>
@endsection
@section('content')

<div class="max-w-2xl mx-auto">

    <x-alert type="error" :message="session('error')" />

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
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-amber-500 to-orange-500">🚚</div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $proveedor->nombre }}</h2>
        </div>

        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">RFC</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $proveedor->rfc) }}"
                        class="w-full px-4 py-3 uppercase border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Correo</label>
                        <input type="email" name="correo" value="{{ old('correo', $proveedor->correo) }}"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('correo') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Dirección</label>
                    <textarea name="direccion" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('direccion', $proveedor->direccion) }}</textarea>
                </div>

                <div>
                    <label class="flex items-center gap-3 p-4 cursor-pointer bg-slate-50 rounded-xl">
                        <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ $proveedor->activo ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Proveedor activo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-between pt-6 mt-8 border-t">
                <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar este proveedor?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-6 py-3 font-medium text-red-600 transition border-2 border-red-300 rounded-xl hover:bg-red-50">🗑️ Eliminar</button>
                </form>
                <div class="flex gap-4">
                    <a href="{{ route('proveedores.index') }}"
                        class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit"
                        class="flex items-center gap-2 px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                        💾 Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection