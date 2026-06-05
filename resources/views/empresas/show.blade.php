{{-- resources/views/empresas/show.blade.php --}}
@extends('layouts.app')

@section('title', $empresa->nombre)
@section('page-title', $empresa->nombre)
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('empresas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Empresas
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">{{ $empresa->nombre }}</span>
    </li>
@endsection

@section('content')

    <div class="max-w-5xl mx-auto">

        <div class="p-8 mb-6 bg-white shadow-lg rounded-3xl">
            <div class="flex items-start justify-between mb-6">
                <div class="flex gap-4">
                    {{-- Logo de la empresa --}}
                    <div class="flex-shrink-0 w-24 h-24 overflow-hidden bg-gray-100 rounded-xl">
                        @if($empresa->logo_url)
                            <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nombre }}" class="object-cover w-full h-full">
                        @else
                            <div class="flex items-center justify-center w-full h-full">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">{{ $empresa->nombre }}</h2>
                        <p class="text-gray-500">RFC: {{ $empresa->rfc }}</p>
                        <span
                            class="inline-block px-2 py-1 mt-1 text-xs text-blue-700 bg-blue-100 rounded-full">{{ $empresa->licencia->nombre }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('empresa.cambiar', $empresa) }}"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-green-500 rounded-xl hover:bg-green-600">
                        🚪 Entrar
                    </a>
                    <a href="{{ route('empresas.edit', $empresa) }}"
                        class="px-4 py-2 text-sm font-medium text-white transition bg-amber-500 rounded-xl hover:bg-amber-600">
                        ✏️ Editar
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2 lg:grid-cols-4">
                <div class="p-3 rounded-lg bg-slate-50">
                    <strong class="block text-xs text-gray-500 uppercase">Dirección</strong>
                    <p class="mt-1 text-slate-700">{{ $empresa->direccion ?? 'N/A' }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50">
                    <strong class="block text-xs text-gray-500 uppercase">Teléfono</strong>
                    <p class="mt-1 text-slate-700">{{ $empresa->telefono ?? 'N/A' }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50">
                    <strong class="block text-xs text-gray-500 uppercase">Fecha inicio</strong>
                    <p class="mt-1 text-slate-700">{{ $empresa->fecha_inicio->format('d/m/Y') }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50">
                    <strong class="block text-xs text-gray-500 uppercase">Fecha vencimiento</strong>
                    <p class="mt-1 font-medium {{ $empresa->fecha_fin->isPast() ? 'text-red-600' : 'text-green-600' }}">
                        {{ $empresa->fecha_fin->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- SUCURSALES -->
        <div class="p-8 bg-white shadow-lg rounded-3xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">Sucursales ({{ $empresa->sucursales->count() }})</h3>
                <a href="{{ route('sucursales.create', ['empresa_id' => $empresa->id]) }}"
                    class="px-3 py-1.5 text-sm text-indigo-700 transition bg-indigo-100 rounded-lg hover:bg-indigo-200">
                    + Nueva sucursal
                </a>
            </div>

            <div class="space-y-3">
                @forelse($empresa->sucursales as $sucursal)
                    <div class="flex items-center justify-between p-4 border border-slate-100 rounded-xl hover:bg-slate-50">
                        <div>
                            <p class="font-medium text-slate-800">📍 {{ $sucursal->nombre }}</p>
                            <p class="text-sm text-gray-500">{{ $sucursal->direccion ?? 'Sin dirección' }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($sucursal->activo)
                                <span class="text-xs text-green-600">● Activo</span>
                            @else
                                <span class="text-xs text-red-600">● Inactivo</span>
                            @endif
                            <a href="{{ route('sucursales.edit', $sucursal) }}"
                                class="text-sm text-indigo-600 hover:underline">Editar</a>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-gray-400">No hay sucursales registradas</p>
                @endforelse
            </div>
        </div>

    </div>

@endsection