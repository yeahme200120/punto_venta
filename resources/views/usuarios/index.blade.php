@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">{{ session('error') }}</div>
@endif

{{-- INFO DE FILTROS --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex flex-wrap items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        @if(isset($sucursalActiva) && $sucursalActiva)
        <span class="px-3 py-1.5 bg-cyan-100 text-cyan-700 rounded-full text-sm font-medium">📍 {{ $sucursalActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $usuarios->count() }} de {{ $usuarios->total() }} usuarios</span>
    </div>

    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('usuarios.export') }}" 
        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-medium shadow flex items-center gap-2">
        📥 Exportar Excel
    </a>
    @endif
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de usuarios</h2>
            <p class="text-sm text-gray-500 mt-1">Gestiona los usuarios de la empresa activa</p>
        </div>
        <a href="{{ route('usuarios.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nuevo usuario
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Roles</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($usuarios as $usuario)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 text-white flex items-center justify-center text-sm font-bold shadow">
                                {{ strtoupper(substr($usuario->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-slate-800">{{ $usuario->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $usuario->email }}</td>
                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $usuario->sucursal->nombre ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach($usuario->roles as $role)
                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($usuario->activo)
                            <span class="text-green-600 text-sm">● Activo</span>
                        @else
                            <span class="text-red-600 text-sm">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('usuarios.show', $usuario) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            <a href="{{ route('usuarios.permisos.edit', $usuario) }}" class="p-2 text-gray-400 hover:text-purple-600" title="Permisos">🔐</a>
                            @if($usuario->id !== auth()->id())
                            <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600">🗑️</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay usuarios</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="px-6 py-4 border-t">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection