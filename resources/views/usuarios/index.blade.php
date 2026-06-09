@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@section('content')

    @if(session('success'))
        <div class="px-4 py-3 mb-6 text-green-700 border border-green-200 bg-green-50 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 mb-6 text-red-700 border border-red-200 bg-red-50 rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- INFO DE FILTROS --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap items-center gap-2">
            @if(isset($empresaActiva))
                <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢
                    {{ $empresaActiva->nombre }}</span>
            @endif
            @if(isset($sucursalActiva) && $sucursalActiva)
                <span class="px-3 py-1.5 bg-cyan-100 text-cyan-700 rounded-full text-sm font-medium">📍
                    {{ $sucursalActiva->nombre }}</span>
            @endif
            <span class="text-sm text-gray-400">Mostrando {{ $usuarios->count() }} de {{ $usuarios->total() }}
                usuarios</span>
        </div>

        @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
            <a href="{{ route('usuarios.export') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
                📥 Exportar Excel
            </a>
        @endif
    </div>

    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="flex flex-col items-start justify-between gap-4 p-6 border-b sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Lista de usuarios</h2>
                <p class="mt-1 text-sm text-gray-500">Gestiona los usuarios de la empresa activa</p>
            </div>
            <a href="{{ route('usuarios.create') }}"
                class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                + Nuevo usuario
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="text-left bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Roles</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($usuarios as $usuario)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center text-sm font-bold text-white rounded-full shadow w-9 h-9 bg-gradient-to-br from-indigo-600 to-cyan-500">
                                        {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-slate-800">{{ $usuario->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $usuario->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $usuario->sucursal->nombre ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($usuario->roles as $role)
                                        <span
                                            class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($usuario->activo)
                                    <span class="text-sm text-green-600">● Activo</span>
                                @else
                                    <span class="text-sm text-red-600">● Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('usuarios.show', $usuario) }}"
                                        class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                                    <a href="{{ route('usuarios.edit', $usuario) }}"
                                        class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                                    {{-- Solo Super Admin puede ver/editar permisos de usuarios --}}
                                    @if(auth()->user()->hasRole('Super Admin'))
                                        <a href="{{ route('usuarios.permisos.edit', $usuario) }}"
                                            class="p-2 text-gray-400 hover:text-purple-600" title="Permisos">
                                            🔐
                                        </a>
                                    @endif
                                    @if($usuario->id !== auth()->id())
                                        <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="inline"
                                            onsubmit="return confirm('¿Eliminar?')">
                                            @csrf @method('DELETE')
                                            <button class="p-2 text-gray-400 hover:text-red-600">🗑️</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay usuarios</td>
                        </tr>
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