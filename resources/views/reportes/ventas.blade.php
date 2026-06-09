@extends('layouts.app')

@section('title', 'Reporte de Ventas')
@section('page-title', 'Reporte de Ventas')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Reporte de Ventas</span></li>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filtros --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <form method="GET" action="{{ route('reportes.ventas') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
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
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                <select name="tipo" class="w-full px-3 py-2 border rounded-xl">
                    <option value="">Todos</option>
                    <option value="contado" {{ request('tipo') == 'contado' ? 'selected' : '' }}>Contado</option>
                    <option value="credito" {{ request('tipo') == 'credito' ? 'selected' : '' }}>Crédito</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 md:col-span-4">
                <button type="submit" class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Filtrar</button>
                <a href="{{ route('reportes.ventas') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Limpiar</a>
                <a href="{{ route('reportes.ventas.exportar', request()->all()) }}" class="px-4 py-2 text-white bg-green-600 rounded-xl hover:bg-green-700">Exportar CSV</a>
            </div>
        </form>
    </div>

    {{-- Tarjetas resumen --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Total de ventas</p>
            <p class="text-2xl font-bold">{{ number_format($totalVentas, 0) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Monto total</p>
            <p class="text-2xl font-bold text-green-600">${{ number_format($totalMonto, 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Contado</p>
            <p class="text-xl font-bold">${{ number_format($totalContado, 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Crédito</p>
            <p class="text-xl font-bold">${{ number_format($totalCredito, 2) }}</p>
        </div>
    </div>

    {{-- Gráfico --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <canvas id="ventasChart" height="100"></canvas>
    </div>

    {{-- Tabla de ventas --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Folio</th>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                        <th class="px-4 py-3 text-right">IVA</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-left">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $venta->folio }}</td>
                        <td class="px-4 py-2">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $venta->cliente->nombre ?? 'Mostrador' }}</td>
                        <td class="px-4 py-2">{{ ucfirst($venta->tipo) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($venta->subtotal, 2) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($venta->iva, 2) }}</td>
                        <td class="px-4 py-2 font-bold text-right">${{ number_format($venta->total, 2) }}</td>
                        <td class="px-4 py-2">{{ $venta->usuario->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No hay ventas en el período seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $ventas->links() }}</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('ventasChart').getContext('2d');
    const ventasPorDia = @json($ventasPorDia);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ventasPorDia.map(item => item.fecha),
            datasets: [{
                label: 'Ventas diarias ($)',
                data: ventasPorDia.map(item => item.total),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true
            }]
        },
        options: { responsive: true }
    });
</script>
@endsection