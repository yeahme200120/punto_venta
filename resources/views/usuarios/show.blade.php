@extends('layouts.app')

@section('title', $usuario->name)
@section('page-title', 'Detalle de usuario')

@section('content')

<div class="max-w-5xl mx-auto">
    <div class="overflow-hidden bg-white shadow-xl rounded-3xl">
        
        {{-- Header con gradiente --}}
        <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-cyan-500">
            <div class="flex items-center gap-6">
                <div class="flex items-center justify-center w-24 h-24 text-4xl font-bold text-white border-4 rounded-full shadow-lg bg-white/20 backdrop-blur-sm border-white/30">
                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-white">{{ $usuario->name }}</h2>
                    <p class="text-indigo-100">{{ $usuario->email }}</p>
                    <div class="flex flex-wrap gap-4 mt-3">
                        <span class="flex items-center gap-1 text-sm text-indigo-100">🏢 {{ $usuario->empresa->nombre ?? 'N/A' }}</span>
                        <span class="flex items-center gap-1 text-sm text-indigo-100">📍 {{ $usuario->sucursal->nombre ?? 'Sin sucursal' }}</span>
                        @if($usuario->activo)
                            <span class="text-sm bg-green-500/30 px-2 py-0.5 rounded-full flex items-center gap-1">🟢 Activo</span>
                        @else
                            <span class="text-sm bg-red-500/30 px-2 py-0.5 rounded-full flex items-center gap-1">🔴 Inactivo</span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-white">#{{ $usuario->id }}</div>
                    <p class="text-sm text-indigo-100">ID de usuario</p>
                </div>
            </div>
        </div>

        {{-- Contenido --}}
        <div class="p-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                
                {{-- Roles --}}
                <div class="p-6 border border-gray-100 bg-gray-50 rounded-2xl">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex items-center justify-center w-10 h-10 bg-indigo-100 rounded-xl">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Roles asignados</h3>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($usuario->roles as $role)
                            <span class="px-4 py-2 text-sm font-medium text-white shadow-sm bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl">
                                🛡️ {{ $role->name }}
                            </span>
                        @empty
                            <div class="flex items-center w-full gap-2 px-4 py-3 text-gray-400 bg-gray-100 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Sin roles asignados</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Permisos Directos --}}
                <div class="p-6 border border-gray-100 bg-gray-50 rounded-2xl">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-100">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Permisos directos</h3>
                    </div>
                    <div class="flex flex-wrap gap-2 overflow-y-auto max-h-64">
                        @php
                            $permisosDirectos = $usuario->getDirectPermissions();
                        @endphp
                        @forelse($permisosDirectos as $permiso)
                            <span class="px-3 py-1.5 bg-cyan-50 text-cyan-700 rounded-lg text-xs font-medium border border-cyan-200 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ str_replace('_', ' ', $permiso->name) }}
                            </span>
                        @empty
                            <div class="flex items-center w-full gap-2 px-4 py-3 text-gray-400 bg-gray-100 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Sin permisos directos asignados</span>
                            </div>
                        @endforelse
                    </div>
                    @if($permisosDirectos->count() > 0)
                        <div class="pt-3 mt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-400">Total: {{ $permisosDirectos->count() }} permisos directos</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Información adicional --}}
            <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-3">
                <div class="p-4 text-center bg-gray-50 rounded-xl">
                    <p class="text-2xl font-bold text-indigo-600">{{ $usuario->roles->count() }}</p>
                    <p class="text-xs tracking-wide text-gray-500 uppercase">Roles asignados</p>
                </div>
                <div class="p-4 text-center bg-gray-50 rounded-xl">
                    <p class="text-2xl font-bold text-cyan-600">{{ $usuario->getDirectPermissions()->count() }}</p>
                    <p class="text-xs tracking-wide text-gray-500 uppercase">Permisos directos</p>
                </div>
                <div class="p-4 text-center bg-gray-50 rounded-xl">
                    <p class="text-2xl font-bold text-green-600">{{ $usuario->getAllPermissions()->count() }}</p>
                    <p class="text-xs tracking-wide text-gray-500 uppercase">Permisos totales</p>
                </div>
            </div>

            {{-- Permisos totales (colapsable) --}}
            <div class="mt-6 overflow-hidden border rounded-xl">
                <button id="btnTogglePermisos" class="flex items-center justify-between w-full px-6 py-4 transition bg-gray-50 hover:bg-gray-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="font-semibold text-slate-700">Todos los permisos ({{ $usuario->getAllPermissions()->count() }})</span>
                    </div>
                    <svg id="iconPermisos" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="contentPermisos" class="hidden p-6 bg-white border-t">
                    @php
                        $permisosAgrupados = $usuario->getAllPermissions()->groupBy(function($permiso) {
                            $parts = explode('_', $permiso->name);
                            return $parts[0] ?? 'general';
                        });
                    @endphp
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($permisosAgrupados as $categoria => $permisos)
                            <div class="p-3 bg-gray-50 rounded-xl">
                                <h4 class="flex items-center gap-1 mb-2 font-semibold text-indigo-600 capitalize">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                    {{ $categoria }}
                                </h4>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($permisos as $permiso)
                                        <span class="px-2 py-0.5 bg-gray-200 text-gray-600 rounded text-xs">
                                            {{ str_replace($categoria . '_', '', $permiso->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('usuarios.index') }}"
                    class="flex items-center gap-2 px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
                <div class="flex gap-3">
                    <a href="{{ route('usuarios.edit', $usuario) }}"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white transition shadow-md bg-amber-500 rounded-xl hover:bg-amber-600">
                        ✏️ Editar usuario
                    </a>
                    @can('modificar_permisos_usuarios')
                    <a href="{{ route('usuarios.permisos.edit', $usuario) }}"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white transition shadow-md bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                        🔐 Gestionar permisos
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnToggle = document.getElementById('btnTogglePermisos');
    const content = document.getElementById('contentPermisos');
    const icon = document.getElementById('iconPermisos');
    
    if (btnToggle) {
        btnToggle.addEventListener('click', function() {
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });
    }
});
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
@endsection