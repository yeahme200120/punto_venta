@extends('layouts.app')

@section('title', 'Sucursal: ' . $sucursal->nombre)
@section('page-title', $sucursal->nombre)

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="flex justify-between items-start mb-6 pb-6 border-b">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 text-white flex items-center justify-center text-xl font-bold shadow">📍</div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">{{ $sucursal->nombre }}</h2>
                        <p class="text-gray-500">{{ $sucursal->empresa->nombre ?? '' }}</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('sucursal.cambiar', $sucursal) }}" class="px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition font-medium shadow text-sm">📍 Seleccionar</a>
                <a href="{{ route('sucursales.edit', $sucursal) }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow text-sm">✏️ Editar</a>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-6">
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Dirección</p>
                <p class="font-semibold">{{ $sucursal->direccion ?? 'No registrada' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Teléfono</p>
                <p class="font-semibold">{{ $sucursal->telefono ?? 'No registrado' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-semibold {{ $sucursal->activo ? 'text-green-600' : 'text-red-600' }}">
                    {{ $sucursal->activo ? '● Activo' : '● Inactivo' }}
                </p>
            </div>
        </div>

        <h3 class="font-bold text-lg text-slate-800 mb-3">Usuarios en esta sucursal ({{ $sucursal->usuarios->count() }})</h3>
        <div class="space-y-2">
            @forelse($sucursal->usuarios as $usuario)
            <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 text-white flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr($usuario->name, 0, 1)) }}
                    </div>
                    <span>{{ $usuario->name }}</span>
                </div>
                <span class="text-sm text-gray-400">{{ $usuario->roles->first()->name ?? 'Sin rol' }}</span>
            </div>
            @empty
            <p class="text-gray-400 text-center py-4">No hay usuarios asignados a esta sucursal</p>
            @endforelse
        </div>

        <a href="{{ route('empresas.show', $sucursal->empresa_id) }}" class="inline-flex items-center gap-2 mt-6 px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
            ← Volver a empresa
        </a>
    </div>
</div>
@endsection