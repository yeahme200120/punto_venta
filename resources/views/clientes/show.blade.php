@extends('layouts.app')

@section('title', 'Detalle del Cliente')
@section('page-title', $cliente->nombre)

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex items-center justify-between gap-3 mb-4">
    <a href="{{ route('clientes.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">← Volver</a>
    
    <div class="flex gap-2">
        @can('editar_clientes')
        <a href="{{ route('clientes.edit', $cliente) }}" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-xl hover:bg-amber-700">✏️ Editar</a>
        @endcan
    </div>
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold text-slate-800">{{ $cliente->nombre }}</h2>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
                <div><label class="text-sm text-gray-500">RFC</label><p class="font-semibold">{{ $cliente->rfc ?? '—' }}</p></div>
                <div><label class="text-sm text-gray-500">Teléfono</label><p class="font-semibold">{{ $cliente->telefono ?? '—' }}</p></div>
                <div><label class="text-sm text-gray-500">Correo</label><p class="font-semibold">{{ $cliente->correo ?? '—' }}</p></div>
            </div>
            <div class="space-y-4">
                <div><label class="text-sm text-gray-500">Tipo</label><p class="font-semibold">{{ ucfirst($cliente->tipo) }}</p></div>
                @if($cliente->tipo == 'credito')
                <div><label class="text-sm text-gray-500">Límite de crédito</label><p class="font-semibold">${{ number_format($cliente->limite_credito, 2) }}</p></div>
                <div><label class="text-sm text-gray-500">Días de crédito</label><p class="font-semibold">{{ $cliente->dias_credito }} días</p></div>
                @endif
                <div><label class="text-sm text-gray-500">Estado</label><p class="font-semibold {{ $cliente->activo ? 'text-green-600' : 'text-red-600' }}">{{ $cliente->activo ? '● Activo' : '● Inactivo' }}</p></div>
            </div>
        </div>
        <div class="mt-6"><label class="text-sm text-gray-500">Dirección</label><p class="font-semibold">{{ $cliente->direccion ?? '—' }}</p></div>
    </div>
</div>
@endsection