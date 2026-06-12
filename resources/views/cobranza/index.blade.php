@extends('layouts.app')

@section('title', 'Cobranza')
@section('page-title', 'Cobranza')

@section('content')
<div class="space-y-5">
    {{-- Info de caja activa --}}
    @if(isset($cajaAbierta) && $cajaAbierta)
    <div class="p-3 bg-green-50 border border-green-200 rounded-xl">
        <div class="flex items-center gap-2 text-sm text-green-700">
            <span>🏦</span>
            <span class="font-medium">{{ $cajaAbierta->caja->nombre }}</span>
            <span class="text-green-400">|</span>
            <span>👤 {{ $cajaAbierta->usuario->name }}</span>
            <span class="text-green-400">|</span>
            <span>💰 ${{ number_format($cajaAbierta->monto_inicial + $cajaAbierta->total_ingresos - $cajaAbierta->total_egresos, 2) }}</span>
        </div>
    </div>
    @endif

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Total en créditos</p>
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($creditos->sum('monto_total'), 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Pagado</p>
            <p class="text-2xl font-bold text-green-600">${{ number_format($creditos->sum('monto_pagado'), 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Saldo pendiente</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($creditos->sum('saldo_pendiente'), 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Créditos activos</p>
            <p class="text-2xl font-bold text-orange-600">{{ $creditos->where('estado', 'activo')->count() }}</p>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <input type="text" id="searchInput" placeholder="Buscar por cliente, folio de venta..." 
            class="w-full py-2 px-4 border rounded-lg focus:ring-2 focus:ring-indigo-500">
    </div>

    {{-- Tabla de créditos --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Venta</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Pagado</th>
                        <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Saldo</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Plazo</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($creditos as $credito)
                    <tr class="transition credito-row hover:bg-gray-50" 
                        data-cliente="{{ strtolower($credito->cliente->nombre) }}"
                        data-folio="{{ strtolower($credito->venta->folio) }}">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $credito->cliente->nombre }}</p>
                            <p class="text-xs text-gray-400">RFC: {{ $credito->cliente->rfc ?? 'N/A' }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-sm text-center text-indigo-600">{{ $credito->venta->folio }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $credito->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-semibold text-right">${{ number_format($credito->monto_total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">${{ number_format($credito->monto_pagado, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $credito->saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">${{ number_format($credito->saldo_pendiente, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ str_replace('_', ' ', $credito->plazo) }}<br><span class="text-xs text-gray-400">{{ $credito->num_pagos }} pagos</span></td>
                        <td class="px-4 py-3 text-center">
                            @if($credito->estado == 'pagado')
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">✅ Pagado</span>
                            @elseif($credito->estado == 'vencido')
                                <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">⏰ Vencido</span>
                            @else
                                <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">🟢 Activo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @can('ver_cobranza')
                            <a href="{{ route('cobranza.show', $credito) }}" class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Ver</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400">No hay créditos registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($creditos->hasPages())<div class="px-4 py-3 border-t">{{ $creditos->links() }}</div>@endif
    </div>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.credito-row').forEach(row => {
        const c = row.dataset.cliente || '', f = row.dataset.folio || '';
        row.style.display = (c.includes(term) || f.includes(term)) ? '' : 'none';
    });
});
</script>
@endsection