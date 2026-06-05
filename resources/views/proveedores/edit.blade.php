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
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div><label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"></div>
                <div><label class="block mb-2 text-sm font-medium text-gray-700">RFC</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $proveedor->rfc) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"></div>
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Correo</label>
                        <input type="email" name="correo" value="{{ old('correo', $proveedor->correo) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"></div>
                </div>
                <div><label class="block mb-2 text-sm font-medium text-gray-700">Dirección</label>
                    <textarea name="direccion" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('direccion', $proveedor->direccion) }}</textarea></div>
                <div><label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ $proveedor->activo ? 'checked' : '' }}>
                    <span class="text-sm font-medium">Proveedor activo</span></label></div>
            </div>
            <div class="flex justify-between mt-8">
                <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                    @csrf @method('DELETE') <button class="px-6 py-3 font-medium text-red-600 transition border-2 border-red-300 rounded-xl hover:bg-red-50">🗑️ Eliminar</button>
                </form>
                <div class="flex gap-4">
                    <a href="{{ route('proveedores.index') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection