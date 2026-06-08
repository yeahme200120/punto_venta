{{-- resources/views/cobranza/historial.blade.php --}}
@extends('layouts.app')

@section('title', 'Historial de Cobranza')
@section('page-title', 'Historial de Cobranza')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cobranza.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Cobranza
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Historial</span>
    </li>
@endsection

@section('content')
<div class="space-y-5">
    {{-- Filtros --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <div class="flex flex-col gap-3 md:flex-row">
            <div class="relative flex-1">
                <svg class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 left-3 top-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Buscar por cliente, folio..." 
                    class="w-full py-2 pr-4 border rounded-lg pl-9 focus:ring-2 focus:ring-indigo-500">
            </div>
            <select id="tipoFilter" class="px-3 py-2 border rounded-lg">
                <option value="">Todos los tipos</option>
                <option value="abono">Abono</option>
                <option value="pago_pagare">Pago de pagaré</option>
                <option value="condonacion">Condonación</option>
            </select>
            <input type="date" id="fechaInicio" class="px-3 py-2 border rounded-lg" placeholder="Fecha inicio">
            <input type="date" id="fechaFin" class="px-3 py-2 border rounded-lg" placeholder="Fecha fin">
            <button id="limpiarFiltros" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
                Limpiar
            </button>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Crédito</th>
                        <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Monto</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($cobranzas as $cobranza)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $cobranza->fecha_cobro->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $cobranza->credito->cliente->nombre }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('cobranza.show', $cobranza->credito) }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $cobranza->credito->venta->folio }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-semibold text-right text-green-600">
                            +${{ number_format($cobranza->monto, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($cobranza->tipo == 'abono')
                                <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">💰 Abono</span>
                            @elseif($cobranza->tipo == 'pago_pagare')
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">✅ Pago de pagaré</span>
                            @else
                                <span class="px-2 py-1 text-xs text-orange-700 bg-orange-100 rounded-full">📝 Condonación</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $cobranza->usuario->name }}</td>
                        <td class="max-w-xs px-4 py-3 text-sm text-gray-500 truncate">
                            {{ $cobranza->observaciones ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            No hay registros de cobranza
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cobranzas->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $cobranzas->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    function filtrar() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const tipo = document.getElementById('tipoFilter').value;
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;

        document.querySelectorAll('tbody tr').forEach(row => {
            const cliente = row.cells[1]?.innerText.toLowerCase() || '';
            const tipoText = row.cells[4]?.innerText.toLowerCase() || '';
            const fecha = row.cells[0]?.innerText.split(' ')[0] || '';

            const matchSearch = !search || cliente.includes(search);
            const matchTipo = !tipo || tipoText.includes(tipo);
            const matchFecha = (!fechaInicio || fecha >= fechaInicio) && (!fechaFin || fecha <= fechaFin);

            row.style.display = (matchSearch && matchTipo && matchFecha) ? '' : 'none';
        });
    }

    document.getElementById('searchInput').addEventListener('input', filtrar);
    document.getElementById('tipoFilter').addEventListener('change', filtrar);
    document.getElementById('fechaInicio').addEventListener('change', filtrar);
    document.getElementById('fechaFin').addEventListener('change', filtrar);
    document.getElementById('limpiarFiltros').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('tipoFilter').value = '';
        document.getElementById('fechaInicio').value = '';
        document.getElementById('fechaFin').value = '';
        filtrar();
    });
</script>
@endsection