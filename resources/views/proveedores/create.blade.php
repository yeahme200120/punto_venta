@extends('layouts.app')

@section('title', 'Nuevo Proveedor')
@section('page-title', 'Nuevo Proveedor')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-alert type="error" :message="session('error')" />
    @if($errors->any())
    <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
        <ul class="list-disc list-inside text-sm text-red-600">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
    @endif

    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-amber-500 to-orange-500">🚚</div>
            <h2 class="text-2xl font-bold text-slate-800">Registrar proveedor</h2>
        </div>
        <form action="{{ route('proveedores.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div><label class="block mb-2 text-sm font-medium">Nombre *</label><input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full border rounded-xl px-4 py-3"></div>
                <div><label class="block mb-2 text-sm font-medium">RFC</label><input type="text" name="rfc" value="{{ old('rfc') }}" class="w-full px-4 py-3 border rounded-xl uppercase"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block mb-2 text-sm font-medium">Teléfono</label><input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full px-4 py-3 border rounded-xl"></div>
                    <div><label class="block mb-2 text-sm font-medium">Correo</label><input type="email" name="correo" value="{{ old('correo') }}" class="w-full px-4 py-3 border rounded-xl"></div>
                </div>
                <div><label class="block mb-2 text-sm font-medium">Dirección</label><textarea name="direccion" rows="2" class="w-full px-4 py-3 border rounded-xl">{{ old('direccion') }}</textarea></div>
            </div>
            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('proveedores.index') }}" class="px-6 py-3 border-2 rounded-xl">Cancelar</a>
                @can('crear_proveedores')
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl">💾 Crear</button>
                @endcan
            </div>
        </form>
    </div>
</div>
@endsection