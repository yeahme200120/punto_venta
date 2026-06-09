@extends('layouts.app')

@section('title', 'Reporte de Cobranza')
@section('page-title', 'Reporte de Cobranza')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Reporte de Cobranza</span></li>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filtros --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <form method="GET" action="{{ route('reportes.cobranza') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="w-full px-3 py-2 border rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="w-full px-3 py-2 border rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                <select name="cliente_id" class="w-full px-3 py-2 border rounded-xl">
                    <option value="">Todos</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="submit" class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Filtrar</button>
                <a href="{{ route('reportes.cobranza') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Limpiar</a>
                <a href="{{ route('reportes.cobranza.exportar', request()->all()) }}" class="px-4 py-2 text-white bg-green-600 rounded-xl hover:bg-green-700">Exportar CSV</a>
            </div>
        </form>
    </div>

    {{-- Tarjetas resumen --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Total cobrado</p>
            <p class="text-2xl font-bold text-green-600">${{ number_format($totalCobrado, 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Saldo pendiente (créditos activos)</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($totalPendiente, 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Créditos activos</p>
            <p class="text-2xl font-bold">{{ $totalCreditos }}</p>
        </div>
    </div>

    {{-- Gráfico --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <canvas id="cobranzaChart" height="100"></canvas>
    </div>

    {{-- Créditos activos --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <h3 class="mb-3 text-lg font-semibold">Créditos activos</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Cliente</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-right">Pagado</th>
                        <th class="px-4 py-2 text-right">Saldo</th>
                        <th class="px-4 py-2 text-left">Vence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($creditosActivos as $credito)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $credito->cliente->nombre }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($credito->monto_total, 2) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($credito->monto_pagado, 2) }}</td>
                        <td class="px-4 py-2 font-bold text-right text-red-600">${{ number_format($credito->saldo_pendiente, 2) }}</td>
                        <td class="px-4 py-2">{{ $credito->fecha_fin->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabla de cobros --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Cliente</th>
                        <th class="px-4 py-2 text-right">Monto</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobros as $cobro)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $cobro->fecha_cobro->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $cobro->credito->cliente->nombre ?? 'N/A' }}</td>
                        <td class="px-4 py-2 font-bold text-right">${{ number_format($cobro->monto, 2) }}</td>
                        <td class="px-4 py-2">{{ ucfirst($cobro->tipo) }}</td>
                        <td class="px-4 py-2">{{ $cobro->usuario->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay cobros en el período seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $cobros->links() }}</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('cobranzaChart').getContext('2d');
    const cobranzaPorDia = @json($cobranzaPorDia);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: cobranzaPorDia.map(item => item.fecha),
            datasets: [{
                label: 'Cobranza diaria ($)',
                data: cobranzaPorDia.map(item => item.total),
                backgroundColor: '#10b981'
            }]
        },
        options: { responsive: true }
    });
</script>
@endsection