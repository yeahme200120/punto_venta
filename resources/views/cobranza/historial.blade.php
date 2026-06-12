@extends('layouts.app')

@section('title', 'Historial de Cobranza')
@section('page-title', 'Historial de Cobranza')

@section('content')
<div class="space-y-5">
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <div class="flex flex-col gap-3 md:flex-row">
            <input type="text" id="searchInput" placeholder="Buscar por cliente, folio..." class="flex-1 py-2 px-4 border rounded-lg">
            <select id="tipoFilter" class="px-3 py-2 border rounded-lg">
                <option value="">Todos</option>
                <option value="abono">Abono</option>
                <option value="pago_pagare">Pago de pagaré</option>
                <option value="condonacion">Condonación</option>
            </select>
            <input type="date" id="fechaInicio" class="px-3 py-2 border rounded-lg">
            <input type="date" id="fechaFin" class="px-3 py-2 border rounded-lg">
            <button id="limpiarFiltros" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Limpiar</button>
        </div>
    </div>

    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr><th class="px-4 py-3 text-xs text-left">Fecha</th><th class="px-4 py-3 text-xs text-left">Cliente</th><th class="px-4 py-3 text-xs text-left">Crédito</th><th class="px-4 py-3 text-xs text-right">Monto</th><th class="px-4 py-3 text-xs text-center">Tipo</th><th class="px-4 py-3 text-xs text-left">Usuario</th></tr>
                </thead>
                <tbody class="divide-y" id="historialBody">
                    @forelse($cobranzas as $cobranza)
                    <tr class="historial-row" data-cliente="{{ strtolower($cobranza->credito->cliente->nombre) }}" data-tipo="{{ $cobranza->tipo }}">
                        <td class="px-4 py-3 text-sm">{{ $cobranza->fecha_cobro->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $cobranza->credito->cliente->nombre }}</td>
                        <td class="px-4 py-3"><a href="{{ route('cobranza.show', $cobranza->credito) }}" class="text-indigo-600">{{ $cobranza->credito->venta->folio }}</a></td>
                        <td class="px-4 py-3 font-semibold text-right text-green-600">+${{ number_format($cobranza->monto, 2) }}</td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-1 text-xs rounded-full {{ $cobranza->tipo == 'abono' ? 'bg-blue-100 text-blue-700' : ($cobranza->tipo == 'pago_pagare' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700') }}">{{ $cobranza->tipo == 'abono' ? 'Abono' : ($cobranza->tipo == 'pago_pagare' ? 'Pago' : 'Condonación') }}</span></td>
                        <td class="px-4 py-3 text-sm">{{ $cobranza->usuario->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No hay registros</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cobranzas->hasPages())<div class="px-4 py-3 border-t">{{ $cobranzas->links() }}</div>@endif
    </div>
</div>

<script>
function filtrar() {
    const s = document.getElementById('searchInput').value.toLowerCase();
    const t = document.getElementById('tipoFilter').value;
    document.querySelectorAll('.historial-row').forEach(row => {
        const c = row.dataset.cliente || '', ti = row.dataset.tipo || '';
        row.style.display = (!s || c.includes(s)) && (!t || ti === t) ? '' : 'none';
    });
}
['searchInput','tipoFilter'].forEach(id => document.getElementById(id)?.addEventListener(id==='searchInput'?'input':'change', filtrar));
document.getElementById('limpiarFiltros')?.addEventListener('click', () => { ['searchInput','tipoFilter','fechaInicio','fechaFin'].forEach(id => { const el = document.getElementById(id); if(el) el.value = ''; }); filtrar(); });
</script>
@endsection