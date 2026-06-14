{{-- resources/views/reportes/caja-dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard de Caja')
@section('page-title', 'Dashboard de Caja')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

{{-- 🔥 INFORMACIÓN DE LA CAJA ACTUAL (SIEMPRE VISIBLE) --}}
<div class="p-4 mb-6 border border-indigo-200 shadow-sm bg-gradient-to-r from-indigo-50 to-cyan-50 rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full">
                <span class="text-2xl">🏦</span>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">{{ $cajaActual->caja->nombre ?? 'Sin caja' }}</h3>
                <p class="text-sm text-gray-500">
                    Código: {{ $cajaActual->caja->codigo ?? 'N/A' }} | 
                    Abierta por: {{ $cajaActual->usuario->name ?? 'N/A' }}
                </p>
                <p class="text-xs text-gray-400">
                    Fecha de apertura: {{ isset($cajaActual) ? $cajaActual->created_at->format('d/m/Y H:i') : 'N/A' }}
                </p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-green-600">
                ${{ number_format($cajaActual->saldoActual() ?? 0, 2) }}
            </p>
            <p class="text-xs text-gray-500">Saldo actual</p>
        </div>
    </div>
</div>

{{-- 🔥 SELECTOR DE CAJA PARA SUPER ADMIN Y ADMINISTRADOR --}}
@if((auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Administrador')) && isset($todasAperturas) && $todasAperturas->count() > 1)
<div class="p-4 mb-6 border border-blue-200 bg-blue-50 rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-lg">🔄</span>
            <span class="font-medium text-gray-700">Cambiar a otra caja:</span>
        </div>
        <div>
            <form action="{{ route('cajas.cambiar') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('reportes.caja.dashboard') }}">
                <select name="apertura_id" onchange="this.form.submit()" class="px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar caja...</option>
                    @foreach($todasAperturas as $cajaOption)
                        <option value="{{ $cajaOption->id }}" {{ $cajaActual->id == $cajaOption->id ? 'selected' : '' }}>
                            {{ $cajaOption->caja->nombre }} ({{ $cajaOption->caja->codigo }}) - {{ $cajaOption->usuario->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Filtros de fecha --}}
<div class="flex flex-wrap gap-4 mb-6">
    <form method="GET" action="{{ route('reportes.caja.dashboard') }}" class="flex flex-wrap items-end gap-2">
        <div>
            <label class="block text-xs font-medium text-gray-500">Fecha inicio</label>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                   class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Fecha fin</label>
            <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                   class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="px-4 py-2 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            📅 Filtrar
        </button>
        <a href="{{ route('reportes.caja.dashboard') }}" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
            Limpiar
        </a>
    </form>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
    <!-- Tarjeta: Total Ingresos -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-500">Total Ingresos</span>
            <span class="text-2xl">💰</span>
        </div>
        <p class="text-2xl font-bold text-green-600">${{ number_format($resumen['total_ingresos'], 2) }}</p>
        <p class="text-sm text-gray-500">{{ $resumen['num_ingresos'] }} transacciones</p>
        @if($resumen['variacion'] != 0)
            <p class="text-xs {{ $resumen['variacion'] > 0 ? 'text-green-500' : 'text-red-500' }} mt-2">
                {{ $resumen['variacion'] > 0 ? '↑' : '↓' }} {{ number_format(abs($resumen['variacion']), 1) }}% vs mes anterior
            </p>
        @endif
    </div>

    <!-- Tarjeta: Total Egresos -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-500">Total Egresos</span>
            <span class="text-2xl">💸</span>
        </div>
        <p class="text-2xl font-bold text-red-600">${{ number_format($resumen['total_egresos'], 2) }}</p>
        <p class="text-sm text-gray-500">{{ $resumen['num_egresos'] }} transacciones</p>
    </div>

    <!-- Tarjeta: Saldo Neto -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-500">Saldo Neto</span>
            <span class="text-2xl">⚖️</span>
        </div>
        <p class="text-2xl font-bold text-indigo-600">${{ number_format($resumen['saldo_neto'], 2) }}</p>
        <p class="text-sm text-gray-500">Ingresos - Egresos</p>
    </div>

    <!-- Tarjeta: Promedio Diario -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-500">Promedio Diario</span>
            <span class="text-2xl">📊</span>
        </div>
        <p class="text-2xl font-bold text-cyan-600">${{ number_format($resumen['total_ingresos'] / max(1, $datos['evolucion_diaria']->count()), 2) }}</p>
        <p class="text-sm text-gray-500">Ingresos por día</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
    <!-- Gráfico: Evolución diaria -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <h3 class="mb-4 text-lg font-semibold">📈 Evolución diaria</h3>
        <canvas id="evolucionChart" width="400" height="200"></canvas>
    </div>

    <!-- Gráfico: Formas de pago -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <h3 class="mb-4 text-lg font-semibold">💳 Ingresos por forma de pago</h3>
        <canvas id="formaPagoChart" width="400" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
    <!-- Top ingresos -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <h3 class="mb-4 text-lg font-semibold">🏆 Top ingresos</h3>
        <div class="space-y-3">
            @forelse($topMovimientos['ingresos'] as $mov)
            <div class="flex items-center justify-between p-3 border rounded-lg">
                <div>
                    <p class="font-medium">{{ $mov->concepto }}</p>
                    <p class="text-xs text-gray-500">{{ $mov->cajaApertura->caja->nombre ?? 'N/A' }} • {{ $mov->usuario->name }}</p>
                </div>
                <p class="font-bold text-green-600">+ ${{ number_format($mov->monto, 2) }}</p>
            </div>
            @empty
            <p class="text-center text-gray-400">No hay ingresos registrados</p>
            @endforelse
        </div>
    </div>

    <!-- Top egresos -->
    <div class="p-6 bg-white shadow-lg rounded-2xl">
        <h3 class="mb-4 text-lg font-semibold">🏆 Top egresos</h3>
        <div class="space-y-3">
            @forelse($topMovimientos['egresos'] as $mov)
            <div class="flex items-center justify-between p-3 border rounded-lg">
                <div>
                    <p class="font-medium">{{ $mov->concepto }}</p>
                    <p class="text-xs text-gray-500">{{ $mov->cajaApertura->caja->nombre ?? 'N/A' }} • {{ $mov->usuario->name }}</p>
                </div>
                <p class="font-bold text-red-600">- ${{ number_format($mov->monto, 2) }}</p>
            </div>
            @empty
            <p class="text-center text-gray-400">No hay egresos registrados</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Movimientos recientes -->
<div class="p-6 mt-6 bg-white shadow-lg rounded-2xl">
    <h3 class="mb-4 text-lg font-semibold">🕐 Movimientos recientes</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="border-b">
                <tr>
                    <th class="px-4 py-2 text-left">Fecha</th>
                    <th class="px-4 py-2 text-left">Caja</th>
                    <th class="px-4 py-2 text-left">Concepto</th>
                    <th class="px-4 py-2 text-left">Usuario</th>
                    <th class="px-4 py-2 text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientosRecientes as $mov)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 text-sm">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2 text-sm">{{ $mov->cajaApertura->caja->nombre ?? 'N/A' }}</td>
                    <td class="px-4 py-2 text-sm">{{ $mov->concepto }}</td>
                    <td class="px-4 py-2 text-sm">{{ $mov->usuario->name }}</td>
                    <td class="px-4 py-2 text-right font-bold {{ $mov->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $mov->tipo == 'ingreso' ? '+' : '-' }} ${{ number_format($mov->monto, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">No hay movimientos registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de evolución
    const evolucionCtx = document.getElementById('evolucionChart').getContext('2d');
    new Chart(evolucionCtx, {
        type: 'line',
        data: {
            labels: @json($evolucionLabels),
            datasets: [
                {
                    label: 'Ingresos',
                    data: @json($evolucionIngresos),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Egresos',
                    data: @json($evolucionEgresos),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Gráfico de formas de pago
    const formaPagoCtx = document.getElementById('formaPagoChart').getContext('2d');
    new Chart(formaPagoCtx, {
        type: 'doughnut',
        data: {
            labels: @json($formaPagoLabels),
            datasets: [{
                data: @json($formaPagoValues),
                backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
</script>
@endsection