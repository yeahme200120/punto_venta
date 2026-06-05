@extends('layouts.app')

@section('title', 'Sucursales - ' . $empresa->nombre)
@section('page-title', 'Sucursales: ' . $empresa->nombre)

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex items-center gap-3 mb-4">
    <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresa->nombre }}</span>
    <span class="text-sm text-gray-400">Mostrando {{ $sucursales->count() }} de {{ $sucursales->total() }} sucursales</span>
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Sucursales</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $empresa->nombre }}</p>
        </div>
        <a href="{{ route('sucursales.create', ['empresa_id' => $empresa->id]) }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nueva sucursal
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Sucursal</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Dirección</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Teléfono</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Usuarios</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sucursales as $sucursal)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 text-white flex items-center justify-center text-xs font-bold shadow">📍</div>
                            <span class="font-medium text-slate-800">{{ $sucursal->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $sucursal->direccion ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $sucursal->telefono ?? '—' }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $sucursal->usuarios_count }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($sucursal->activo)
                            <span class="text-green-600 text-sm">● Activo</span>
                        @else
                            <span class="text-red-600 text-sm">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('sucursal.cambiar', $sucursal) }}" class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs hover:bg-blue-200" title="Seleccionar">📍</a>
                            <a href="{{ route('sucursales.show', $sucursal) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('sucursales.edit', $sucursal) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            @if($sucursal->usuarios_count == 0)
                            <form action="{{ route('sucursales.destroy', $sucursal) }}" method="POST" class="inline"
                                onsubmit="return confirm('¿Eliminar esta sucursal?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600" title="Eliminar">🗑️</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay sucursales registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $sucursales->links() }}
    </div>
</div>
@endsection