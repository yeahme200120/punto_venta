@extends('layouts.app')

@section('title', 'Licencia: ' . $licencia->nombre)
@section('page-title', $licencia->nombre)

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="flex justify-between items-start mb-6 pb-6 border-b">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">{{ $licencia->nombre }}</h2>
                <p class="text-gray-500">${{ number_format($licencia->precio, 2) }} · {{ $licencia->dias }} días</p>
            </div>
            <a href="{{ route('licencias.edit', $licencia) }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow text-sm">✏️ Editar</a>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-indigo-600">{{ $licencia->max_usuarios }}</p>
                <p class="text-sm text-gray-500 mt-1">Max Usuarios</p>
            </div>
            <div class="bg-gradient-to-br from-cyan-50 to-teal-50 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-cyan-600">{{ $licencia->max_sucursales }}</p>
                <p class="text-sm text-gray-500 mt-1">Max Sucursales</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-purple-600">{{ $licencia->empresas->count() }}</p>
                <p class="text-sm text-gray-500 mt-1">Empresas activas</p>
            </div>
        </div>

        <h3 class="font-bold text-lg text-slate-800 mb-3">Empresas con esta licencia</h3>
        <div class="space-y-2">
            @forelse($licencia->empresas as $empresa)
            <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">🏢</div>
                    <span class="font-medium">{{ $empresa->nombre }}</span>
                </div>
                <span class="text-sm {{ $empresa->fecha_fin < now() ? 'text-red-600' : 'text-gray-400' }}">
                    Vence: {{ $empresa->fecha_fin->format('d/m/Y') }}
                </span>
            </div>
            @empty
            <p class="text-gray-400 text-center py-6">Ninguna empresa usa esta licencia</p>
            @endforelse
        </div>

        <a href="{{ route('licencias.index') }}" class="inline-flex items-center gap-2 mt-6 px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
            ← Volver a licencias
        </a>
    </div>
</div>
@endsection