@extends('layouts.app')

@section('title', 'Editar Caja')
@section('page-title', 'Editar Caja')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-amber-600 to-orange-500">
                ✏️
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Editar caja</h2>
            <p class="mt-2 text-gray-500">Actualiza los datos de la caja</p>
            <p class="text-sm text-gray-400">Código: {{ $caja->codigo }}</p>
        </div>

        <form action="{{ route('cajas.cajas.update', $caja) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Sucursal *</label>
                    <select name="sucursal_id" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar sucursal...</option>
                        @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ old('sucursal_id', $caja->sucursal_id) == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('sucursal_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre de la caja *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $caja->nombre) }}" required 
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('nombre') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="descripcion" rows="3" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('descripcion', $caja->descripcion) }}</textarea>
                    @error('descripcion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="permite_multiple" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('permite_multiple', $caja->permite_multiple) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Permite múltiples aperturas en el mismo día</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('activo', $caja->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Caja activa</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('cajas.cajas.index') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-amber-600 to-orange-500 rounded-xl hover:from-amber-700 hover:to-orange-600">
                    💾 Actualizar caja
                </button>
            </div>
        </form>
    </div>
</div>
@endsection