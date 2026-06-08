{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Panel de Control')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Dashboard</span></li>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Tarjetas KPI --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Ventas hoy -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Ventas hoy</p>
                    <p class="text-2xl font-bold text-indigo-600">${{ number_format($ventasHoy ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full">💰</div>
            </div>
        </div>
        <!-- Cobranza hoy -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Cobranza hoy</p>
                    <p class="text-2xl font-bold text-green-600">${{ number_format($cobranzaHoy ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">✅</div>
            </div>
        </div>
        <!-- Créditos activos -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Créditos activos</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $creditosActivos ?? 0 }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">📋</div>
            </div>
        </div>
        <!-- Productos stock bajo -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Stock bajo</p>
                    <p class="text-2xl font-bold text-red-600">{{ $productosStockBajo ?? 0 }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">⚠️</div>
            </div>
        </div>
    </div>

    {{-- Segunda fila de KPI --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Ventas del mes</p>
            <p class="text-xl font-bold">${{ number_format($ventasMes ?? 0, 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Cobranza del mes</p>
            <p class="text-xl font-bold text-green-600">${{ number_format($cobranzaMes ?? 0, 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Saldo pendiente (créditos)</p>
            <p class="text-xl font-bold text-red-600">${{ number_format($saldoPendiente ?? 0, 2) }}</p>
        </div>
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <p class="text-sm text-gray-500">Usuarios activos</p>
            <p class="text-xl font-bold">{{ $totalUsuarios }}</p>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Ventas por mes -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Ventas por mes</h3>
                <div class="flex space-x-2">
                    <button onclick="cambiarTipoGrafica('ventas', 'bar')" class="px-2 py-1 text-sm bg-indigo-100 rounded">Barras</button>
                    <button onclick="cambiarTipoGrafica('ventas', 'pie')" class="px-2 py-1 text-sm bg-gray-100 rounded">Pastel</button>
                    <a href="{{ route('dashboard.exportar', ['tipo' => 'ventas_mes']) }}" class="px-2 py-1 text-sm text-white bg-green-600 rounded hover:bg-green-700">Exportar</a>
                </div>
            </div>
            <canvas id="graficaVentas" height="250"></canvas>
        </div>

        <!-- Cobranza por mes -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Cobranza por mes</h3>
                <div class="flex space-x-2">
                    <button onclick="cambiarTipoGrafica('cobranza', 'bar')" class="px-2 py-1 text-sm bg-indigo-100 rounded">Barras</button>
                    <button onclick="cambiarTipoGrafica('cobranza', 'pie')" class="px-2 py-1 text-sm bg-gray-100 rounded">Pastel</button>
                    <a href="{{ route('dashboard.exportar', ['tipo' => 'cobranza_mes']) }}" class="px-2 py-1 text-sm text-white bg-green-600 rounded hover:bg-green-700">Exportar</a>
                </div>
            </div>
            <canvas id="graficaCobranza" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Ingresos por forma de pago (pastel fijo) -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Ingresos por forma de pago (último mes)</h3>
                <a href="{{ route('dashboard.exportar', ['tipo' => 'ingresos_forma_pago']) }}" class="px-2 py-1 text-sm text-white bg-green-600 rounded hover:bg-green-700">Exportar</a>
            </div>
            <canvas id="graficaFormaPago" height="250"></canvas>
        </div>

        <!-- Top 5 clientes -->
        <div class="p-4 bg-white border shadow-sm rounded-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Top 5 clientes (último año)</h3>
                <a href="{{ route('dashboard.exportar', ['tipo' => 'top_clientes']) }}" class="px-2 py-1 text-sm text-white bg-green-600 rounded hover:bg-green-700">Exportar</a>
            </div>
            <canvas id="graficaTopClientes" height="250"></canvas>
        </div>
    </div>

    {{-- Información de licencia --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Licencia de {{ $empresa->nombre }}</p>
                <p class="text-lg font-semibold">{{ $licencia ? $licencia->nombre : 'Sin licencia' }}</p>
                <p class="text-sm {{ $diasRestantes < 30 ? 'text-red-600' : 'text-gray-600' }}">
                    @if($diasRestantes > 0)
                        Vigencia: {{ $diasRestantes }} días restantes
                    @else
                        Licencia vencida
                    @endif
                </p>
            </div>
            @if($empresa->logo_url)
                <img src="{{ $empresa->logo_url }}" class="object-contain w-16 h-16">
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos para gráficas (obtenidos del controlador)
    const ventasMeses = @json(array_keys($ventasPorMes ?? []));
    const ventasValores = @json(array_values($ventasPorMes ?? []));
    const cobranzaMeses = @json(array_keys($cobranzaPorMes ?? []));
    const cobranzaValores = @json(array_values($cobranzaPorMes ?? []));
    const formasPago = @json(array_keys($ingresosFormaPago ?? []));
    const montosFormaPago = @json(array_values($ingresosFormaPago ?? []));
    const topClientesNombres = @json(isset($topClientes) ? $topClientes->pluck('nombre') : []);
    const topClientesMontos = @json(isset($topClientes) ? $topClientes->pluck('total') : []);

    let chartVentas, chartCobranza, chartFormaPago, chartTopClientes;

    function iniciarGraficas() {
        // Gráfica ventas (barras por defecto)
        const ctxVentas = document.getElementById('graficaVentas').getContext('2d');
        chartVentas = new Chart(ctxVentas, {
            type: 'bar',
            data: {
                labels: ventasMeses,
                datasets: [{
                    label: 'Ventas ($)',
                    data: ventasValores,
                    backgroundColor: 'rgba(79, 70, 229, 0.6)',
                    borderColor: '#4f46e5',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });

        // Gráfica cobranza
        const ctxCobranza = document.getElementById('graficaCobranza').getContext('2d');
        chartCobranza = new Chart(ctxCobranza, {
            type: 'bar',
            data: {
                labels: cobranzaMeses,
                datasets: [{
                    label: 'Cobranza ($)',
                    data: cobranzaValores,
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: '#22c55e',
                    borderWidth: 1
                }]
            },
            options: { responsive: true }
        });

        // Gráfica formas de pago (pastel)
        const ctxFormaPago = document.getElementById('graficaFormaPago').getContext('2d');
        chartFormaPago = new Chart(ctxFormaPago, {
            type: 'pie',
            data: {
                labels: formasPago,
                datasets: [{
                    data: montosFormaPago,
                    backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6']
                }]
            },
            options: { responsive: true }
        });

        // Gráfica top clientes (barras horizontales)
        const ctxTop = document.getElementById('graficaTopClientes').getContext('2d');
        chartTopClientes = new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topClientesNombres,
                datasets: [{
                    label: 'Total gastado ($)',
                    data: topClientesMontos,
                    backgroundColor: '#f97316'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true
            }
        });
    }

    function cambiarTipoGrafica(grafica, tipo) {
        let chart, labels, datos, titulo;
        if (grafica === 'ventas') {
            chart = chartVentas;
            labels = ventasMeses;
            datos = ventasValores;
            titulo = 'Ventas ($)';
        } else if (grafica === 'cobranza') {
            chart = chartCobranza;
            labels = cobranzaMeses;
            datos = cobranzaValores;
            titulo = 'Cobranza ($)';
        } else {
            return;
        }
        chart.destroy();
        const ctx = document.getElementById(`grafica${grafica.charAt(0).toUpperCase() + grafica.slice(1)}`).getContext('2d');
        let nuevoTipo = (tipo === 'bar') ? 'bar' : 'pie';
        let nuevoChart = new Chart(ctx, {
            type: nuevoTipo,
            data: {
                labels: labels,
                datasets: [{
                    label: titulo,
                    data: datos,
                    backgroundColor: nuevoTipo === 'bar' ? 'rgba(79, 70, 229, 0.6)' : ['#4f46e5', '#22c55e', '#ef4444', '#f59e0b', '#3b82f6'],
                    borderColor: '#4f46e5',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
        if (grafica === 'ventas') chartVentas = nuevoChart;
        else chartCobranza = nuevoChart;
    }

    document.addEventListener('DOMContentLoaded', iniciarGraficas);
</script>
@endsection