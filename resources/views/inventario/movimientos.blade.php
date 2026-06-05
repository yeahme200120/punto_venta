@extends('layouts.app')

@section('title', 'Movimientos de Inventario')
@section('page-title', 'Movimientos de Inventario')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex justify-between items-center mb-4">
    <span class="text-sm text-gray-400">Mostrando {{ $movimientos->count() }} de {{ $movimientos->total() }} movimientos</span>
    <a href="{{ route('inventario.movimientos.create') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">+ Nuevo movimiento</a>
</div>

{{-- FILTROS --}}
<form method="GET" class="bg-white rounded-2xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="text-xs text-gray-500">Tipo</label>
        <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos</option>
            <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
            <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
            <option value="transferencia" {{ request('tipo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
            <option value="ajuste" {{ request('tipo') == 'ajuste' ? 'selected' : '' }}>Ajuste</option>
        </select>
    </div>
    <div>
        <label class="text-xs text-gray-500">Producto</label>
        <select name="producto_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach($productos as $prod) <option value="{{ $prod->id }}" {{ request('producto_id') == $prod->id ? 'selected' : '' }}>{{ $prod->nombre }}</option> @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs text-gray-500">Desde</label>
        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-xs text-gray-500">Hasta</label>
        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    </div>
    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">🔍 Filtrar</button>
    <a href="{{ route('inventario.movimientos') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">Limpiar</a>
</form>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Fecha</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Tipo</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Producto/Insumo</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Cantidad</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Costo Total</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Usuario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($movimientos as $mov)
                <tr class="hover:bg-gray-50 transition text-sm">
                    <td class="px-6 py-4">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $mov->tipo == 'entrada' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $mov->tipo == 'salida' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $mov->tipo == 'transferencia' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $mov->tipo == 'ajuste' ? 'bg-amber-100 text-amber-700' : '' }}">
                            {{ ucfirst($mov->tipo) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ $mov->producto->nombre ?? $mov->insumo->nombre ?? '—' }}</td>
                    <td class="px-6 py-4 text-center font-semibold">{{ $mov->cantidad }}</td>
                    <td class="px-6 py-4 text-center">${{ number_format($mov->costo_total, 2) }}</td>
                    <td class="px-6 py-4">{{ $mov->usuario->name ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Sin movimientos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t">{{ $movimientos->links() }}</div>
</div>
@endsection