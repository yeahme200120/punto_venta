{{-- resources/views/unidades-medida/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Unidad de Medida: ' . $unidad_medidas->nombre)
@section('page-title', $unidad_medidas->nombre)
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
        <span class="font-medium text-gray-700">{{ $unidad_medidas->nombre }}</span>
    </li>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="p-8 mb-6 bg-white shadow-lg rounded-3xl">
        <div class="flex items-start justify-between pb-6 mb-6 border-b">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 text-3xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">
                    📏
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">{{ $unidad_medidas->nombre }}</h2>
                    <div class="flex gap-2 mt-1">
                        <span class="px-2 py-1 font-mono text-xs font-medium text-indigo-700 bg-indigo-100 rounded-full">
                            Clave: {{ $unidad_medidas->clave }}
                        </span>
                        @if($unidad_medidas->simbolo)
                        <span class="px-2 py-1 font-mono text-xs font-medium text-gray-700 bg-gray-100 rounded-full">
                            Símbolo: {{ $unidad_medidas->simbolo }}
                        </span>
                        @endif
                        @if($unidad_medidas->activo)
                            <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">
                                ● Activo
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">
                                ● Inactivo
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('unidades-medida.edit', $unidad_medidas) }}" 
                   class="px-4 py-2 text-sm font-medium text-white transition shadow bg-amber-500 rounded-xl hover:bg-amber-600">
                    ✏️ Editar
                </a>
                <button onclick="toggleActivo({{ $unidad_medidas->id }})"
                    class="px-4 py-2 {{ $unidad_medidas->activo ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-xl transition font-medium shadow text-sm">
                    {{ $unidad_medidas->activo ? '🔴 Desactivar' : '🟢 Activar' }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="space-y-4">
                <div class="p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs font-medium text-gray-400 uppercase">Tipo de unidad</p>
                    <p class="mt-1 text-lg font-semibold text-slate-800">{{ $unidad_medidas->tipo }}</p>
                </div>
                
                <div class="p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs font-medium text-gray-400 uppercase">Clave</p>
                    <p class="mt-1 font-mono text-lg font-bold text-indigo-600">{{ $unidad_medidas->clave }}</p>
                </div>
                
                <div class="p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs font-medium text-gray-400 uppercase">Nombre</p>
                    <p class="mt-1 text-lg font-semibold text-slate-800">{{ $unidad_medidas->nombre }}</p>
                </div>
            </div>
            
            <div class="space-y-4">
                @if($unidad_medidas->simbolo)
                <div class="p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs font-medium text-gray-400 uppercase">Símbolo</p>
                    <p class="mt-1 font-mono text-lg font-semibold text-slate-800">{{ $unidad_medidas->simbolo }}</p>
                </div>
                @endif
                
                <div class="p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs font-medium text-gray-400 uppercase">Fecha de creación</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $unidad_medidas->created_at->format('d/m/Y H:i') }}</p>
                </div>
                
                <div class="p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs font-medium text-gray-400 uppercase">Última actualización</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $unidad_medidas->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        @if($unidad_medidas->descripcion)
        <div class="p-4 mt-6 bg-slate-50 rounded-xl">
            <p class="mb-2 text-xs font-medium text-gray-400 uppercase">Descripción</p>
            <p class="text-slate-700">{{ $unidad_medidas->descripcion }}</p>
        </div>
        @endif
    </div>

    {{-- INSUMOS QUE USAN ESTA UNIDAD --}}
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">
                🧱 Insumos que usan esta unidad ({{ $unidad_medidas->insumos->count() }})
            </h3>
            <a href="{{ route('insumos.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                Ver todos los insumos →
            </a>
        </div>

        @if($unidad_medidas->insumos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Código</th>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Stock</th>
                        <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Costo Unitario</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($unidad_medidas->insumos as $insumo)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm text-indigo-600">{{ $insumo->codigo ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $insumo->nombre }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-medium {{ $insumo->stock <= $insumo->stock_minimo ? 'text-red-600' : 'text-slate-800' }}">
                                {{ number_format($insumo->stock, 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-slate-700">${{ number_format($insumo->costo_unitario, 2) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($insumo->activo)
                                <span class="text-sm text-green-600">● Activo</span>
                            @else
                                <span class="text-sm text-red-600">● Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('insumos.show', $insumo) }}" class="p-1 text-gray-400 hover:text-indigo-600" title="Ver">
                                    👁️
                                </a>
                                <a href="{{ route('insumos.edit', $insumo) }}" class="p-1 text-gray-400 hover:text-amber-600" title="Editar">
                                    ✏️
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="py-12 text-center">
            <div class="mb-3 text-4xl">📏</div>
            <p class="text-gray-400">No hay insumos registrados con esta unidad de medida</p>
            <a href="{{ route('insumos.create') }}" class="inline-block mt-3 text-sm text-indigo-600 hover:text-indigo-800">
                + Crear insumo
            </a>
        </div>
        @endif
    </div>

    <div class="flex justify-between mt-6">
        <a href="{{ route('unidades-medida.index') }}" 
           class="inline-flex items-center gap-2 px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
            ← Volver al listado
        </a>
        @if($unidad_medidas->activo && $unidad_medidas->insumos->count() === 0)
        <form action="{{ route('unidades-medida.destroy', $unidad_medidas) }}" method="POST" 
              onsubmit="return confirm('¿Eliminar esta unidad de medida?')">
            @csrf @method('DELETE')
            <button type="submit" class="px-6 py-3 font-medium text-red-600 transition border-2 border-red-300 rounded-xl hover:bg-red-50">
                🗑️ Eliminar unidad
            </button>
        </form>
        @endif
    </div>
</div>

<script>
function toggleActivo(unidadId) {
    fetch(`/unidades-medida/${unidadId}/toggle-activo`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(response => response.json()).then(data => {
        if (data.success) {
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al cambiar estado',
                confirmButtonColor: '#4f46e5'
            });
        }
    }).catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión',
            confirmButtonColor: '#4f46e5'
        });
    });
}
</script>
@endsection