@extends('layouts.app')

@section('title', 'Categoría: ' . $categoria->nombre)
@section('page-title', $categoria->nombre)

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="flex justify-between items-start mb-6 pb-6 border-b">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">{{ $categoria->nombre }}</h2>
                <p class="text-gray-500">{{ $categoria->descripcion ?? 'Sin descripción' }}</p>
            </div>
            
            {{-- Editar: solo con permiso --}}
            @can('editar_categorias')
            <a href="{{ route('categorias.edit', $categoria) }}" 
               class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow text-sm">
                ✏️ Editar
            </a>
            @endcan
        </div>

        <h3 class="font-bold text-lg text-slate-800 mb-3">
            📦 Productos en esta categoría ({{ $categoria->productos->count() }})
        </h3>
        
        <div class="space-y-2">
            @forelse($categoria->productos as $producto)
            <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-slate-50">
                <span class="font-medium text-slate-700">{{ $producto->nombre }}</span>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-400">Stock: {{ $producto->stock }}</span>
                    <span class="text-sm font-medium text-green-600">${{ number_format($producto->precio_venta, 2) }}</span>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-center py-4">Sin productos en esta categoría</p>
            @endforelse
        </div>

        <a href="{{ route('categorias.index') }}" 
           class="inline-flex items-center gap-2 mt-6 px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
            ← Volver al listado
        </a>
    </div>
</div>
@endsection