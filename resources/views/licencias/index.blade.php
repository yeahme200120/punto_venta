@extends('layouts.app')

@section('title', 'Licencias')
@section('page-title', 'Licencias')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <span class="text-sm text-gray-400">Mostrando {{ $licencias->count() }} de {{ $licencias->total() }} licencias</span>
    <a href="{{ route('licencias.export') }}" 
        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-medium shadow flex items-center gap-2">
        📥 Exportar Excel
    </a>
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Licencias del sistema</h2>
            <p class="text-sm text-gray-500 mt-1">Administra los planes disponibles</p>
        </div>
        <a href="{{ route('licencias.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nueva licencia
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Plan</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Días</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Usuarios</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Sucursales</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Precio</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Empresas</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($licencias as $licencia)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 text-white flex items-center justify-center text-xs font-bold">📜</div>
                            <span class="font-semibold text-slate-800">{{ $licencia->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">{{ $licencia->dias }}</span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm">{{ $licencia->max_usuarios }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $licencia->max_sucursales }}</td>
                    <td class="px-6 py-4 text-center text-sm font-semibold text-green-700">${{ number_format($licencia->precio, 2) }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $licencia->empresas_count }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($licencia->activo)
                            <span class="text-green-600 text-sm">● Activo</span>
                        @else
                            <span class="text-red-600 text-sm">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('licencias.show', $licencia) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('licencias.edit', $licencia) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            @if($licencia->empresas_count == 0)
                            <form action="{{ route('licencias.destroy', $licencia) }}" method="POST" class="inline"
                                onsubmit="return confirm('¿Eliminar esta licencia?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600" title="Eliminar">🗑️</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">No hay licencias registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $licencias->links() }}
    </div>
</div>
@endsection