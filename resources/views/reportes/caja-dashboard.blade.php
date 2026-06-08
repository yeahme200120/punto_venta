@extends('layouts.app')

@section('title', 'Dashboard de Caja')
@section('page-title', 'Dashboard de Caja')

@section('content')
<div class="space-y-6">
    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="p-6 bg-white shadow-lg rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Ingresos</p>
                    <p class="text-2xl font-bold text-green-600">${{ number_format($resumen['total_ingresos'], 2) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <span class="text-2xl">💰</span>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white shadow-lg rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Egresos</p>
                    <p class="text-2xl font-bold text-red-600">${{ number_format($resumen['total_egresos'], 2) }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <span class="text-2xl">💸</span>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white shadow-lg rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Saldo Neto</p>
                    <p class="text-2xl font-bold text-blue-600">${{ number_format($resumen['saldo_neto'], 2) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <span class="text-2xl">📊</span>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white shadow-lg rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Transacciones</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $resumen['num_ingresos'] + $resumen['num_egresos'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <span class="text-2xl">📝</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Evolución diaria --}}
        <div class="p-6 bg-white shadow-lg rounded-2xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">📈 Evolución Diaria</h3>
            <canvas id="evolucionChart" height="250"></canvas>
        </div>

        {{-- Forma de pago --}}
        <div class="p-6 bg-white shadow-lg rounded-2xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">💳 Por Forma de Pago</h3>
            <canvas id="formaPagoChart" height="250"></canvas>
        </div>
    </div>

    {{-- Top movimientos --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="overflow-hidden bg-white shadow-lg rounded-2xl">
            <div class="p-4 border-b bg-green-50">
                <h3 class="font-bold text-green-800">🏆 Top Ingresos</h3>
            </div>
            <div class="divide-y">
                @forelse($topMovimientos['ingresos'] as $mov)
                <div class="flex items-center justify-between p-4">
                    <div>
                        <p class="font-medium">{{ $mov->concepto }}</p>
                        <p class="text-xs text-gray-500">{{ $mov->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="font-bold text-green-600">+${{ number_format($mov->monto, 2) }}</p>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">No hay ingresos registrados</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden bg-white shadow-lg rounded-2xl">
            <div class="p-4 border-b bg-red-50">
                <h3 class="font-bold text-red-800">🏆 Top Egresos</h3>
            </div>
            <div class="divide-y">
                @forelse($topMovimientos['egresos'] as $mov)
                <div class="flex items-center justify-between p-4">
                    <div>
                        <p class="font-medium">{{ $mov->concepto }}</p>
                        <p class="text-xs text-gray-500">{{ $mov->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="font-bold text-red-600">-${{ number_format($mov->monto, 2) }}</p>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">No hay egresos registrados</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Evolución diaria - datos desde variables preparadas
const evolucionLabels = @json($evolucionLabels);
const evolucionIngresos = @json($evolucionIngresos);
const evolucionEgresos = @json($evolucionEgresos);

const ctxEvolucion = document.getElementById('evolucionChart').getContext('2d');
new Chart(ctxEvolucion, {
    type: 'line',
    data: {
        labels: evolucionLabels,
        datasets: [
            {
                label: 'Ingresos',
                data: evolucionIngresos,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Egresos',
                data: evolucionEgresos,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.raw.toFixed(2);
                    }
                }
            }
        }
    }
});

// Forma de pago - datos desde variables preparadas
const formaPagoLabels = @json($formaPagoLabels);
const formaPagoValues = @json($formaPagoValues);

const ctxFormaPago = document.getElementById('formaPagoChart').getContext('2d');
new Chart(ctxFormaPago, {
    type: 'doughnut',
    data: {
        labels: formaPagoLabels,
        datasets: [{
            data: formaPagoValues,
            backgroundColor: ['#22c55e', '#3b82f6', '#a855f7', '#f59e0b', '#06b6d4', '#ef4444', '#8b5cf6']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        return label + ': $' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>
@endsection