{{-- resources/views/formas_pago/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Forma de Pago')
@section('page-title', 'Nueva Forma de Pago')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><a href="{{ route('formas_pago.index') }}" class="text-gray-500 hover:text-indigo-600">Formas de Pago</a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Nueva</span></li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="p-6 bg-white border shadow-sm rounded-2xl">
        <form action="{{ route('formas_pago.store') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Clave *</label>
                    <input type="text" name="clave" value="{{ old('clave') }}" required
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Ej: EFECTIVO, TARJETA_DEBITO, TRANSFERENCIA</p>
                    @error('clave') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Icono (emoji)</label>
                    <input type="text" name="icono" value="{{ old('icono') }}" maxlength="10"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                           placeholder="Ej: 💵, 💳, 🏦">
                    <p class="mt-1 text-xs text-gray-400">Un emoji para identificar rápidamente</p>
                    @error('icono') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Orden</label>
                    <input type="number" name="orden" value="{{ old('orden', 0) }}" min="0"
                           class="w-32 px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('orden') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="activo" {{ old('activo', true) ? 'checked' : '' }}>
                        <span class="text-sm">Activo</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="requiere_referencia" {{ old('requiere_referencia') ? 'checked' : '' }}>
                        <span class="text-sm">Requiere número de referencia</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="requiere_autorizacion" {{ old('requiere_autorizacion') ? 'checked' : '' }}>
                        <span class="text-sm">Requiere autorización especial</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t">
                <a href="{{ route('formas_pago.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Cancelar</a>
                <button type="submit" class="px-6 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection