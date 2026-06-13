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
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-cyan-500">
            <h2 class="text-xl font-bold text-white">Agregar forma de pago al catálogo global</h2>
            <p class="text-sm text-indigo-100">Una vez creada, estará disponible para todas las empresas</p>
        </div>

        <form action="{{ route('formas_pago.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Clave *</label>
                    <input type="text" name="clave" value="{{ old('clave') }}" required
                           class="w-full px-4 py-3 font-mono border rounded-xl focus:ring-2 focus:ring-indigo-500"
                           placeholder="ej: EFECTIVO, TARJETA_DEBITO">
                    <p class="mt-1 text-xs text-gray-400">Identificador único (en mayúsculas, sin espacios)</p>
                    @error('clave') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                           placeholder="Ej: Efectivo, Tarjeta de Débito">
                    @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Icono (emoji)</label>
                    <input type="text" name="icono" value="{{ old('icono') }}" maxlength="10"
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                           placeholder="Ej: 💵, 💳, 🏦, 📱">
                    <p class="mt-1 text-xs text-gray-400">Un emoji para identificar rápidamente la forma de pago</p>
                    @error('icono') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Orden global</label>
                    <input type="number" name="orden" value="{{ old('orden', 0) }}" min="0"
                           class="w-32 px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Orden de aparición en el catálogo global</p>
                    @error('orden') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 p-4 bg-gray-50 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="activo_global" {{ old('activo_global', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Activar globalmente</span>
                        <span class="text-xs text-gray-400">(Si está activa, estará disponible para todas las empresas por defecto)</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="requiere_referencia" {{ old('requiere_referencia') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Requiere número de referencia</span>
                        <span class="text-xs text-gray-400">(Ej: folio de transferencia, número de cheque)</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="requiere_autorizacion" {{ old('requiere_autorizacion') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <span class="text-sm font-medium text-gray-700">Requiere autorización especial</span>
                        <span class="text-xs text-gray-400">(Solo para montos grandes o formas de pago especiales)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t">
                <a href="{{ route('formas_pago.index') }}" class="px-6 py-3 transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                    Cancelar
                </a>
                <button type="submit" class="px-8 py-3 text-white transition shadow-md bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                    💾 Guardar forma de pago
                </button>
            </div>
        </form>
    </div>
</div>
@endsection