{{-- resources/views/reportes/caja-dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard de Caja')
@section('page-title', 'Dashboard de Caja')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Reportes</span>
    </li>
@endsection

@section('content')

<div class="space-y-6">
    {{-- Filtros --}}
    <div class="p-6 bg-white shadow-lg rounded-3xl">
        <form method="GET" action="{{ route('reportes.caja.dashboard') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                    class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                    class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <button type="submit" class="px-6 py-2 text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
                    🔍 Aplicar filtros
                </button>
            </div>
            <div>
                <button type="button" onclick="setFiltroMesActual()" class="px-6 py-2 text-indigo-600 transition border border-indigo-600 rounded-xl hover:bg-indigo-50">
                    📅 Mes actual
                </button>
            </div>
            <div>
                <button type="button" onclick="setFiltroMesAnterior()" class="px-6 py-2 text-indigo-600 transition border border-indigo-600 rounded-xl hover:bg-indigo-50">
                    📅 Mes anterior
                </button>
            </div>
        </form>
    </div>

    {{-- Cards resumen --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="p-5 text-white shadow-lg bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Ingresos</p>
                    <p class="text-2xl font-bold">${{ number_format($resumen['total_ingresos'], 2) }}</p>
                    <p class="mt-1 text-xs opacity-75">{{ $resumen['num_ingresos'] }} movimientos</p>
                </div>
                <div class="text-3xl">💰</div>
            </div>
            <div class="flex items-center gap-1 mt-2 text-xs">
                @if($resumen['variacion'] > 0)
                    <span>📈 +{{ number_format($resumen['variacion'], 1) }}% vs mes anterior</span>
                @elseif($resumen['variacion'] < 0)
                    <span>📉 {{ number_format($resumen['variacion'], 1) }}% vs mes anterior</span>
                @else
                    <span>➡️ Sin cambios vs mes anterior</span>
                @endif
            </div>
        </div>
        
        <div class="p-5 text-white shadow-lg bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Egresos</p>
                    <p class="text-2xl font-bold">${{ number_format($resumen['total_egresos'], 2) }}</p>
                    <p class="mt-1 text-xs opacity-75">{{ $resumen['num_egresos'] }} movimientos</p>
                </div>
                <div class="text-3xl">💸</div>
            </div>
        </div>
        
        <div class="p-5 text-white shadow-lg bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Saldo Neto</p>
                    <p class="text-2xl font-bold">${{ number_format($resumen['saldo_neto'], 2) }}</p>
                    <p class="mt-1 text-xs opacity-75">Ingresos - Egresos</p>
                </div>
                <div class="text-3xl">⚖️</div>
            </div>
        </div>
        
        <div class="p-5 text-white shadow-lg bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Ticket Promedio</p>
                    <p class="text-2xl font-bold">${{ number_format($resumen['promedio_ingreso'], 2) }}</p>
                    <p class="mt-1 text-xs opacity-75">Por movimiento</p>
                </div>
                <div class="text-3xl">🎫</div>
            </div>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Evolución diaria --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">📈 Evolución diaria</h3>
            <canvas id="chartEvolucion" class="w-full h-64"></canvas>
        </div>
        
        {{-- Distribución por forma de pago --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">💳 Ingresos por forma de pago</h3>
            <canvas id="chartFormaPago" class="w-full h-64"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Categorías de ingresos --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">📊 Ingresos por categoría</h3>
            <canvas id="chartCategoriaIngresos" class="w-full h-64"></canvas>
        </div>
        
        {{-- Categorías de egresos --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">📉 Egresos por categoría</h3>
            <canvas id="chartCategoriaEgresos" class="w-full h-64"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Movimientos por día de semana --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">📅 Actividad por día</h3>
            <canvas id="chartPorDia" class="w-full h-64"></canvas>
        </div>
        
        {{-- Top movimientos --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">🏆 Top movimientos</h3>
            <div class="space-y-3">
                <div>
                    <p class="mb-2 text-sm font-medium text-green-600">💰 Mayores ingresos</p>
                    <div class="space-y-1">
                        @foreach($topMovimientos['ingresos'] as $mov)
                        <div class="flex items-center justify-between p-2 text-sm rounded-lg bg-green-50">
                            <span>{{ $mov->concepto }}</span>
                            <span class="font-bold text-green-600">+ ${{ number_format($mov->monto, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-3">
                    <p class="mb-2 text-sm font-medium text-red-600">💸 Mayores egresos</p>
                    <div class="space-y-1">
                        @foreach($topMovimientos['egresos'] as $mov)
                        <div class="flex items-center justify-between p-2 text-sm rounded-lg bg-red-50">
                            <span>{{ $mov->concepto }}</span>
                            <span class="font-bold text-red-600">- ${{ number_format($mov->monto, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Movimientos recientes --}}
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="p-6 border-b">
            <h3 class="text-lg font-bold text-slate-800">📋 Movimientos recientes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Concepto</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Forma de pago</th>
                        <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($movimientosRecientes as $mov)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <div>
                                <span class="font-medium">{{ $mov->concepto }}</span>
                                @if($mov->referencia)
                                <p class="text-xs text-gray-400">Ref: {{ $mov->referencia }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($mov->tipo == 'ingreso')
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">💰 Ingreso</span>
                            @else
                                <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">💸 Egreso</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-center capitalize">{{ $mov->forma_pago }}</td>
                        <td class="px-6 py-4 text-right font-bold {{ $mov->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $mov->tipo == 'ingreso' ? '+' : '-' }} ${{ number_format($mov->monto, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            No hay movimientos en el período seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Evolución diaria
const evolucionData = @json($datos['evolucion_diaria']->map(function($item) {
    return [
        'fecha' => \Carbon\Carbon::parse($item->fecha)->format('d/m'),
        'ingresos' => floatval($item->ingresos),
        'egresos' => floatval($item->egresos)
    ];
}));

new Chart(document.getElementById('chartEvolucion'), {
    type: 'line',
    data: {
        labels: evolucionData.map(d => d.fecha),
        datasets: [
            {
                label: 'Ingresos',
                data: evolucionData.map(d => d.ingresos),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Egresos',
                data: evolucionData.map(d => d.egresos),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4
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

// Forma de pago
const formaPagoData = @json($datos['forma_pago']->map(function($item) {
    return [
        'label' => ucfirst(str_replace('_', ' ', $item->forma_pago)),
        'value' => floatval($item->total)
    ];
}));

new Chart(document.getElementById('chartFormaPago'), {
    type: 'doughnut',
    data: {
        labels: formaPagoData.map(d => d.label),
        datasets: [{
            data: formaPagoData.map(d => d.value),
            backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Categorías de ingresos
const categoriaIngresosData = @json($datos['categoria_ingresos']->map(function($item) {
    return [
        'label' => ucfirst(str_replace('_', ' ', $item->categoria)),
        'value' => floatval($item->total)
    ];
}));

new Chart(document.getElementById('chartCategoriaIngresos'), {
    type: 'bar',
    data: {
        labels: categoriaIngresosData.map(d => d.label),
        datasets: [{
            label: 'Monto ($)',
            data: categoriaIngresosData.map(d => d.value),
            backgroundColor: '#10b981'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Categorías de egresos
const categoriaEgresosData = @json($datos['categoria_egresos']->map(function($item) {
    return [
        'label' => ucfirst(str_replace('_', ' ', $item->categoria)),
        'value' => floatval($item->total)
    ];
}));

new Chart(document.getElementById('chartCategoriaEgresos'), {
    type: 'bar',
    data: {
        labels: categoriaEgresosData.map(d => d.label),
        datasets: [{
            label: 'Monto ($)',
            data: categoriaEgresosData.map(d => d.value),
            backgroundColor: '#ef4444'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Por día de semana
const porDiaData = @json($datos['por_dia_semana']->map(function($item) {
    return [
        'dia' => $item->dia,
        'ingresos' => floatval($item->ingresos),
        'egresos' => floatval($item->egresos)
    ];
}));

new Chart(document.getElementById('chartPorDia'), {
    type: 'bar',
    data: {
        labels: porDiaData.map(d => d.dia),
        datasets: [
            {
                label: 'Ingresos',
                data: porDiaData.map(d => d.ingresos),
                backgroundColor: '#10b981'
            },
            {
                label: 'Egresos',
                data: porDiaData.map(d => d.egresos),
                backgroundColor: '#ef4444'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Filtros rápidos
function setFiltroMesActual() {
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    
    document.querySelector('input[name="fecha_inicio"]').value = formatDate(primerDia);
    document.querySelector('input[name="fecha_fin"]').value = formatDate(ultimoDia);
    document.querySelector('form').submit();
}

function setFiltroMesAnterior() {
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
    const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
    
    document.querySelector('input[name="fecha_inicio"]').value = formatDate(primerDia);
    document.querySelector('input[name="fecha_fin"]').value = formatDate(ultimoDia);
    document.querySelector('form').submit();
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
</script>
@endsection