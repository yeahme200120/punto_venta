@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles')

@section('content')

<x-alert type="error" :message="session('error')" />
<x-alert type="success" :message="session('success')" />

{{-- INFO DE FILTROS --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex flex-wrap items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium flex items-center gap-1">
            🏢 {{ $empresaActiva->nombre }}
        </span>
        @endif
        <span class="text-sm text-gray-400">
            Mostrando {{ $roles->count() }} de {{ $roles->total() }} roles
        </span>
    </div>

    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('roles.export') }}" 
        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-medium shadow flex items-center gap-2">
        📥 Exportar Excel
    </a>
    @endif
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de roles</h2>
            <p class="text-sm text-gray-500 mt-1">Gestiona los roles y permisos del sistema</p>
        </div>
        <a href="{{ route('roles.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nuevo rol
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Rol</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Permisos</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Usuarios</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($roles as $role)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 text-white flex items-center justify-center text-xs font-bold shadow">
                                {{ strtoupper(substr($role->name, 0, 1)) }}
                            </div>
                            <span class="font-semibold text-slate-800">{{ $role->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                            {{ $role->permissions->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                            {{ $role->users_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('roles.show', $role) }}" class="p-2 text-gray-400 hover:text-indigo-600 transition" title="Ver">👁️</a>
                            <a href="{{ route('roles.edit', $role) }}" class="p-2 text-gray-400 hover:text-amber-600 transition" title="Editar">✏️</a>
                            @if($role->name !== 'Super Admin')
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline"
                                onsubmit="return confirm('¿Eliminar este rol?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600 transition" title="Eliminar">🗑️</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">No hay roles creados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="px-6 py-4 border-t">
        {{ $roles->links() }}
    </div>
</div>

@endsection