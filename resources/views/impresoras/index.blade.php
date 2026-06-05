@extends('layouts.app')

@section('title', 'Impresoras')
@section('page-title', 'Impresoras')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $impresoras->count() }} de {{ $impresoras->total() }} impresoras</span>
    </div>
    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('impresoras.export') }}" 
        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-medium shadow flex items-center gap-2">
        📥 Exportar Excel
    </a>
    @endif
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Impresoras configuradas</h2>
            <p class="text-sm text-gray-500 mt-1">Administra las impresoras de la empresa</p>
        </div>
        <a href="{{ route('impresoras.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nueva impresora
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Nombre</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Tipo</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Conexión</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Sucursal</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($impresoras as $impresora)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-slate-800">{{ $impresora->nombre }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $impresora->tipo == 'ticket' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $impresora->tipo == 'factura' ? 'bg-purple-100 text-purple-700' : '' }}
                            {{ $impresora->tipo == 'etiqueta' ? 'bg-amber-100 text-amber-700' : '' }}">
                            {{ ucfirst($impresora->tipo) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $impresora->puerto ? 'Puerto: ' . $impresora->puerto : '' }}
                        {{ $impresora->ip ? 'IP: ' . $impresora->ip : '' }}
                        {{ !$impresora->puerto && !$impresora->ip ? '—' : '' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $impresora->sucursal->nombre ?? 'Todas' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($impresora->activo)
                            <span class="text-green-600 text-sm">● Activo</span>
                        @else
                            <span class="text-red-600 text-sm">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('impresoras.show', $impresora) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('impresoras.edit', $impresora) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            <form action="{{ route('impresoras.destroy', $impresora) }}" method="POST" class="inline"
                                onsubmit="return confirm('¿Eliminar esta impresora?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600" title="Eliminar">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay impresoras configuradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $impresoras->links() }}
    </div>
</div>
@endsection