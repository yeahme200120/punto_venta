@extends('layouts.app')

@section('title', $usuario->name)
@section('page-title', 'Detalle de usuario')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-white rounded-3xl shadow-lg p-8">

        <div class="flex items-center gap-6 mb-8 pb-6 border-b">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 text-white flex items-center justify-center text-3xl font-bold shadow-lg">
                {{ strtoupper(substr($usuario->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">{{ $usuario->name }}</h2>
                <p class="text-gray-500">{{ $usuario->email }}</p>
                <div class="flex gap-4 mt-2">
                    <span class="text-sm text-gray-400">🏢 {{ $usuario->empresa->nombre ?? 'N/A' }}</span>
                    <span class="text-sm text-gray-400">📍 {{ $usuario->sucursal->nombre ?? 'Sin sucursal' }}</span>
                    @if($usuario->activo)
                        <span class="text-sm text-green-600">● Activo</span>
                    @else
                        <span class="text-sm text-red-600">● Inactivo</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="font-bold text-lg text-slate-800 mb-3">Roles</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($usuario->roles as $role)
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">
                            {{ $role->name }}
                        </span>
                    @empty
                        <p class="text-gray-400">Sin roles asignados</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h3 class="font-bold text-lg text-slate-800 mb-3">Permisos directos</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($usuario->getDirectPermissions() as $permiso)
                        <span class="px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs">
                            {{ str_replace('_', ' ', $permiso->name) }}
                        </span>
                    @empty
                        <p class="text-gray-400">Sin permisos directos</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('usuarios.index') }}"
                class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
                ← Volver
            </a>
            <div class="flex gap-3">
                <a href="{{ route('usuarios.edit', $usuario) }}"
                    class="px-6 py-3 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow">
                    ✏️ Editar
                </a>
                <a href="{{ route('usuarios.permisos.edit', $usuario) }}"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow">
                    🔐 Permisos
                </a>
            </div>
        </div>
    </div>
</div>

@endsection