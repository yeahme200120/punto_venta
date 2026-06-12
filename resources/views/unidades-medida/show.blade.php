@extends('layouts.app')

@section('title', 'Unidad: ' . $unidad_medida->nombre)
@section('page-title', $unidad_medida->nombre)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="p-8 mb-6 bg-white shadow-lg rounded-3xl">
        <div class="flex items-start justify-between pb-6 mb-6 border-b">
            <div>
                <h2 class="text-2xl font-bold">{{ $unidad_medida->nombre }}</h2>
                <div class="flex gap-2 mt-1">
                    <span class="px-2 py-1 text-xs bg-indigo-100 text-indigo-700 rounded-full">Clave: {{ $unidad_medida->clave }}</span>
                    @if($unidad_medida->simbolo)<span class="px-2 py-1 text-xs bg-gray-100 rounded-full">{{ $unidad_medida->simbolo }}</span>@endif
                </div>
            </div>
            @can('editar_unidades_medida')
            <a href="{{ route('unidades-medida.edit', $unidad_medida) }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-sm">✏️ Editar</a>
            @endcan
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-slate-50 rounded-xl"><p class="text-xs text-gray-400">Tipo</p><p class="font-semibold">{{ $unidad_medida->tipo }}</p></div>
            <div class="p-4 bg-slate-50 rounded-xl"><p class="text-xs text-gray-400">Clave</p><p class="font-mono font-bold text-indigo-600">{{ $unidad_medida->clave }}</p></div>
            @if($unidad_medida->simbolo)<div class="p-4 bg-slate-50 rounded-xl"><p class="text-xs text-gray-400">Símbolo</p><p class="font-mono">{{ $unidad_medida->simbolo }}</p></div>@endif
        </div>
    </div>
    <a href="{{ route('unidades-medida.index') }}" class="px-6 py-3 border-2 rounded-xl">← Volver</a>
</div>
@endsection