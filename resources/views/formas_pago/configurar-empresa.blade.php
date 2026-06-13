{{-- resources/views/formas_pago/configurar-empresa.blade.php --}}
@extends('layouts.app')

@section('title', 'Configurar Formas de Pago')
@section('page-title', 'Configurar Formas de Pago')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><a href="{{ route('formas_pago.index') }}" class="text-gray-500 hover:text-indigo-600">Formas de Pago</a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Configurar {{ $empresa->nombre }}</span></li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        {{-- Header --}}
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-cyan-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Configurar formas de pago</h2>
                    <p class="text-sm text-indigo-100">Selecciona las formas de pago disponibles para: {{ $empresa->nombre }}</p>
                </div>
                <div class="px-3 py-1 text-sm font-semibold text-indigo-700 bg-white rounded-full">
                    ID: {{ $empresa->id }}
                </div>
            </div>
        </div>

        {{-- Formulario --}}
        <form method="POST" action="{{ route('formas_pago.configurar.empresa.update', $empresa->id) }}" class="p-6">
            @csrf

            <div class="p-3 mb-4 text-sm text-blue-700 bg-blue-50 rounded-xl">
                <span class="font-semibold">ℹ️ Información:</span> Las formas de pago marcadas como activas estarán disponibles en el punto de venta y operaciones de caja para esta empresa.
            </div>

            <div class="space-y-3">
                @foreach($todasFormas as $forma)
                <div class="flex items-center justify-between p-4 transition border rounded-xl hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 text-2xl bg-gray-100 rounded-full">
                            {{ $forma->icono ?? '💳' }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $forma->nombre }}</p>
                            <p class="font-mono text-xs text-gray-500">{{ $forma->clave }}</p>
                            @if($forma->requiere_referencia)
                                <span class="inline-block mt-1 text-xs text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">📝 Requiere referencia</span>
                            @endif
                            @if($forma->requiere_autorizacion)
                                <span class="inline-block mt-1 text-xs text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full">🔐 Requiere autorización</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        {{-- Orden personalizado --}}
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-gray-500">Orden:</label>
                            <input type="number" name="orden_{{ $forma->id }}" 
                                   value="{{ $ordenes[$forma->id] ?? $forma->orden }}" 
                                   min="0" max="999"
                                   class="w-16 px-2 py-1 text-center border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        {{-- Toggle activo/inactivo --}}
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="formas_activas[]" value="{{ $forma->id }}"
                                   class="sr-only peer"
                                   {{ (isset($configuraciones[$forma->id]) && $configuraciones[$forma->id]) ? 'checked' : '' }}>
                            <div class="h-6 transition bg-gray-200 rounded-full w-11 peer peer-checked:bg-green-600"></div>
                            <div class="absolute w-4 h-4 transition bg-white rounded-full left-1 top-1 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 pt-4 mt-8 border-t">
                <a href="{{ route('formas_pago.index') }}" class="px-6 py-2 transition border rounded-xl hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 text-white transition shadow-md bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                    💾 Guardar configuración
                </button>
            </div>
        </form>
    </div>

    {{-- Información adicional --}}
    <div class="p-4 mt-4 text-sm text-gray-500 bg-gray-50 rounded-xl">
        <p class="mb-2 font-semibold">📌 Notas:</p>
        <ul class="space-y-1 list-disc list-inside">
            <li>El <strong>catálogo global</strong> se administra desde el listado principal de formas de pago.</li>
            <li>Las formas de pago desactivadas en el catálogo global no aparecerán en esta lista.</li>
            <li>El <strong>orden personalizado</strong> define la prioridad de aparición en el punto de venta.</li>
            <li>Los cambios aquí realizados afectan solo a <strong>{{ $empresa->nombre }}</strong>.</li>
        </ul>
    </div>
</div>
@endsection