@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

    <!-- Total usuarios -->
    <div class="bg-white rounded-3xl shadow-lg p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-2xl">
                👥
            </div>
            <div>
                <p class="text-sm text-gray-500">Usuarios</p>
                <p class="text-2xl font-bold text-slate-800">{{ $totalUsuarios }}</p>
            </div>
        </div>
    </div>

    <!-- Licencia -->
    <div class="bg-white rounded-3xl shadow-lg p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-100 flex items-center justify-center text-2xl">
                📋
            </div>
            <div>
                <p class="text-sm text-gray-500">Licencia</p>
                <p class="text-2xl font-bold text-slate-800">{{ $licencia->nombre }}</p>
            </div>
        </div>
    </div>

    <!-- Días restantes -->
    <div class="bg-white rounded-3xl shadow-lg p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $diasRestantes <= 7 ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center text-2xl">
                📅
            </div>
            <div>
                <p class="text-sm text-gray-500">Días restantes</p>
                <p class="text-2xl font-bold {{ $diasRestantes <= 7 ? 'text-red-600' : 'text-slate-800' }}">
                    @if($diasRestantes < 0)
                        Vencida
                    @else
                        {{ $diasRestantes }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Empresa -->
    <div class="bg-white rounded-3xl shadow-lg p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-2xl">
                🏢
            </div>
            <div>
                <p class="text-sm text-gray-500">Empresa</p>
                <p class="text-2xl font-bold text-slate-800 truncate">{{ Str::limit($empresa->nombre, 15) }}</p>
            </div>
        </div>
    </div>

</div>

<!-- Info licencia -->
<div class="bg-white rounded-3xl shadow-lg p-6">
    <h3 class="font-bold text-lg text-slate-800 mb-4">Información de la licencia</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <p class="text-sm text-gray-500">Tipo de licencia</p>
            <p class="font-semibold text-slate-800">{{ $licencia->nombre }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Máximo de usuarios</p>
            <p class="font-semibold text-slate-800">{{ $licencia->max_usuarios }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Máximo de sucursales</p>
            <p class="font-semibold text-slate-800">{{ $licencia->max_sucursales }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Fecha de inicio</p>
            <p class="font-semibold text-slate-800">{{ $empresa->fecha_inicio?->format('d/m/Y') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Fecha de vencimiento</p>
            <p class="font-semibold {{ $diasRestantes <= 7 ? 'text-red-600' : 'text-slate-800' }}">
                {{ $empresa->fecha_fin?->format('d/m/Y') }}
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Estado</p>
            @if($empresa->activo && $diasRestantes >= 0)
                <span class="text-green-600 font-semibold">● Activo</span>
            @else
                <span class="text-red-600 font-semibold">● Inactivo</span>
            @endif
        </div>
    </div>
</div>

@endsection