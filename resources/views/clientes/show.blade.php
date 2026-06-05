@extends('layouts.app')

@section('title', 'Cliente: ' . $cliente->nombre)
@section('page-title', $cliente->nombre)

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="flex justify-between items-start mb-6 pb-6 border-b">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white flex items-center justify-center text-xl font-bold shadow">👤</div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">{{ $cliente->nombre }}</h2>
                        <p class="text-gray-500">{{ $cliente->rfc ?? 'Sin RFC' }}</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('clientes.edit', $cliente) }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow text-sm">✏️ Editar</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Tipo</p>
                <span class="px-2 py-1 rounded-full text-xs font-medium mt-1 inline-block
                    {{ $cliente->tipo == 'credito' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                    {{ ucfirst($cliente->tipo) }}
                </span>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Teléfono</p>
                <p class="font-semibold">{{ $cliente->telefono ?? 'No registrado' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Correo</p>
                <p class="font-semibold">{{ $cliente->correo ?? 'No registrado' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Sucursal</p>
                <p class="font-semibold">{{ $cliente->sucursal->nombre ?? 'Sin sucursal' }}</p>
            </div>
            @if($cliente->tipo == 'credito')
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Límite de crédito</p>
                <p class="font-semibold">${{ number_format($cliente->limite_credito, 2) }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Días de crédito</p>
                <p class="font-semibold">{{ $cliente->dias_credito }}</p>
            </div>
            @endif
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Dirección</p>
                <p class="font-semibold">{{ $cliente->direccion ?? 'No registrada' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-semibold {{ $cliente->activo ? 'text-green-600' : 'text-red-600' }}">
                    {{ $cliente->activo ? '● Activo' : '● Inactivo' }}
                </p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Fecha de registro</p>
                <p class="font-semibold">{{ $cliente->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <a href="{{ route('clientes.index') }}" class="inline-flex items-center gap-2 mt-6 px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
            ← Volver a clientes
        </a>
    </div>
</div>
@endsection