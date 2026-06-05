@extends('layouts.app')

@section('title', 'Impresora: ' . $impresora->nombre)
@section('page-title', $impresora->nombre)

@section('content')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="flex justify-between items-start mb-6 pb-6 border-b">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">{{ $impresora->nombre }}</h2>
                <span class="px-2 py-1 rounded-full text-xs font-medium mt-2 inline-block
                    {{ $impresora->tipo == 'ticket' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $impresora->tipo == 'factura' ? 'bg-purple-100 text-purple-700' : '' }}
                    {{ $impresora->tipo == 'etiqueta' ? 'bg-amber-100 text-amber-700' : '' }}">
                    {{ ucfirst($impresora->tipo) }}
                </span>
            </div>
            <a href="{{ route('impresoras.edit', $impresora) }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow text-sm">✏️ Editar</a>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Sucursal</p>
                <p class="font-semibold">{{ $impresora->sucursal->nombre ?? 'Todas las sucursales' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Puerto</p>
                <p class="font-semibold">{{ $impresora->puerto ?? 'No configurado' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Dirección IP</p>
                <p class="font-semibold">{{ $impresora->ip ?? 'No configurada' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-semibold {{ $impresora->activo ? 'text-green-600' : 'text-red-600' }}">
                    {{ $impresora->activo ? '● Activo' : '● Inactivo' }}
                </p>
            </div>
        </div>

        <a href="{{ route('impresoras.index') }}" class="inline-flex items-center gap-2 mt-6 px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
            ← Volver
        </a>
    </div>
</div>
@endsection