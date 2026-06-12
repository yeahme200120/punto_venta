@extends('layouts.app')

@section('title', 'Insumo: ' . $insumo->nombre)
@section('page-title', $insumo->nombre)

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="flex justify-between items-start mb-6 pb-6 border-b">
            <div>
                <h2 class="text-2xl font-bold">{{ $insumo->nombre }}</h2>
                <p class="text-gray-500">{{ $insumo->proveedor->nombre ?? 'Sin proveedor' }}</p>
            </div>
            
            {{-- Botón editar solo con permiso --}}
            @can('editar_insumos')
            <a href="{{ route('insumos.edit', $insumo) }}" 
               class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow text-sm">
                ✏️ Editar
            </a>
            @endcan
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($insumo->stock, 2) }}</p>
                <p class="text-xs text-gray-500">Stock</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-green-600">${{ number_format($insumo->costo_unitario, 2) }}</p>
                <p class="text-xs text-gray-500">Costo Unitario</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-purple-600">{{ $insumo->unidad_medida ?? '—' }}</p>
                <p class="text-xs text-gray-500">Unidad</p>
            </div>
        </div>

        @if($insumo->descripcion)
        <div class="mb-6">
            <h3 class="font-bold text-lg mb-2">📝 Descripción</h3>
            <p class="text-gray-600">{{ $insumo->descripcion }}</p>
        </div>
        @endif

        @if($insumo->productos->count() > 0)
        <h3 class="font-bold text-lg mb-3">📦 Productos que usan este insumo</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($insumo->productos as $prod)
                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                    {{ $prod->nombre }}
                </span>
            @endforeach
        </div>
        @else
        <p class="text-gray-400 text-sm">Este insumo no está asociado a ningún producto.</p>
        @endif
    </div>
</div>
@endsection