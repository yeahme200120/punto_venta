@extends('layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $clientes->count() }} de {{ $clientes->total() }} clientes</span>
    </div>
    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('clientes.export') }}" 
        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-medium shadow flex items-center gap-2">
        📥 Exportar Excel
    </a>
    @endif
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de clientes</h2>
            <p class="text-sm text-gray-500 mt-1">Administra los clientes de la empresa</p>
        </div>
        <a href="{{ route('clientes.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nuevo cliente
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Cliente</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Tipo</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Teléfono</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Correo</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Crédito</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clientes as $cliente)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white flex items-center justify-center text-xs font-bold shadow">👤</div>
                            <span class="font-medium text-slate-800">{{ $cliente->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $cliente->tipo == 'credito' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($cliente->tipo) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $cliente->telefono ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $cliente->correo ?? '—' }}</td>
                    <td class="px-6 py-4 text-center text-sm">
                        @if($cliente->tipo == 'credito')
                            ${{ number_format($cliente->limite_credito, 2) }} / {{ $cliente->dias_credito }} días
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($cliente->activo)
                            <span class="text-green-600 text-sm">● Activo</span>
                        @else
                            <span class="text-red-600 text-sm">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('clientes.show', $cliente) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('clientes.edit', $cliente) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline"
                                onsubmit="return confirm('¿Eliminar este cliente?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600" title="Eliminar">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No hay clientes registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $clientes->links() }}
    </div>
</div>
@endsection