{{-- resources/views/cajas/reporte-dia.blade.php --}}
@extends('layouts.app')

@section('title', 'Reporte de Caja')
@section('page-title', 'Reporte de Caja')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cajas.cajas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Cajas
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Reporte</span>
    </li>
@endsection

@section('content')

<div class="max-w-5xl mx-auto">
    {{-- 🔥 INFORMACIÓN DE LA CAJA ACTUAL Y SELECTOR --}}
    <div class="p-4 mb-6 border border-indigo-200 shadow-sm bg-gradient-to-r from-indigo-50 to-cyan-50 rounded-xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full">
                    <span class="text-2xl">🏦</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">{{ $apertura->caja->nombre }}</h3>
                    <p class="text-sm text-gray-500">Código: {{ $apertura->caja->codigo }} | Abierta por: {{ $apertura->usuario->name }}</p>
                    <p class="text-xs text-gray-400">Fecha de apertura: {{ $apertura->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-green-600">${{ number_format($resumen['saldo_esperado'], 2) }}</p>
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
                    <input type="hidden" name="redirect" value="{{ route('cajas.reporte.dia') }}">
                    <select name="apertura_id" onchange="this.form.submit()" class="px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar caja...</option>
                        @foreach($todasAperturas as $cajaOption)
                            <option value="{{ $cajaOption->id }}" {{ $apertura->id == $cajaOption->id ? 'selected' : '' }}>
                                {{ $cajaOption->caja->nombre }} ({{ $cajaOption->caja->codigo }}) - {{ $cajaOption->usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Header del reporte --}}
    <div class="p-8 mb-6 bg-white shadow-lg rounded-3xl">
        <div class="mb-6 text-center">
            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 text-3xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">📊</div>
            <h2 class="text-2xl font-bold text-slate-800">Reporte de Caja</h2>
            <p class="text-gray-500">{{ $resumen['fecha'] }}</p>
            <p class="text-sm text-gray-400">Caja: {{ $apertura->caja->nombre }} ({{ $apertura->caja->codigo }})</p>
            <p class="text-sm text-gray-400">Usuario: {{ $apertura->usuario->name }}</p>
            <p class="text-sm text-gray-400">Sucursal: {{ $apertura->sucursal->nombre ?? 'N/A' }}</p>
        </div>

        {{-- Totales principales --}}
        <div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-4">
            <div class="p-4 text-center bg-green-50 rounded-xl">
                <p class="text-2xl font-bold text-green-600">${{ number_format(floatval($resumen['apertura']), 2) }}</p>
                <p class="text-xs text-gray-500">Apertura</p>
            </div>
            <div class="p-4 text-center bg-blue-50 rounded-xl">
                <p class="text-2xl font-bold text-blue-600">+ ${{ number_format(floatval($resumen['total_ingresos']), 2) }}</p>
                <p class="text-xs text-gray-500">Ingresos</p>
            </div>
            <div class="p-4 text-center bg-red-50 rounded-xl">
                <p class="text-2xl font-bold text-red-600">- ${{ number_format(floatval($resumen['total_egresos']), 2) }}</p>
                <p class="text-xs text-gray-500">Egresos</p>
            </div>
            <div class="p-4 text-center bg-indigo-50 rounded-xl">
                <p class="text-2xl font-bold text-indigo-600">${{ number_format(floatval($resumen['saldo_esperado']), 2) }}</p>
                <p class="text-xs text-gray-500">Saldo Esperado</p>
            </div>
        </div>

        {{-- Formas de pago - Dinámico --}}
        <div class="mb-8">
            <h3 class="mb-4 text-lg font-bold text-slate-800">💰 Desglose por forma de pago</h3>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                @foreach($resumen['por_forma_pago'] as $forma => $monto)
                    @if($monto > 0)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="text-sm capitalize">
                            @php
                                $icono = '💰';
                                $formaNombre = ucfirst(str_replace('_', ' ', $forma));
                                if(isset($formasPago) && $formasPago->count() > 0) {
                                    $formaPagoObj = $formasPago->firstWhere('clave', $forma);
                                    if($formaPagoObj && $formaPagoObj->icono) {
                                        $icono = $formaPagoObj->icono;
                                        $formaNombre = $formaPagoObj->nombre;
                                    }
                                }
                            @endphp
                            {!! $icono !!} {{ $formaNombre }}
                        </span>
                        <span class="font-semibold">${{ number_format($monto, 2) }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Resumen adicional --}}
        <div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-2">
            <div class="p-4 bg-purple-50 rounded-xl">
                <h4 class="font-semibold text-purple-800">📈 Promedios</h4>
                <div class="mt-2 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span>Ticket promedio:</span>
                        <span class="font-bold">${{ number_format($resumen['promedio_venta'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Ingreso promedio:</span>
                        <span class="font-bold">${{ number_format($resumen['promedio_ingreso'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-orange-50 rounded-xl">
                <h4 class="font-semibold text-orange-800">📊 Estadísticas</h4>
                <div class="mt-2 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span>Total transacciones:</span>
                        <span class="font-bold">{{ $resumen['total_transacciones'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Total clientes:</span>
                        <span class="font-bold">{{ $resumen['total_clientes'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Movimientos pendientes de autorización --}}
        @if(isset($movimientosPendientes) && $movimientosPendientes->count() > 0)
        <div class="p-4 mb-6 border border-yellow-200 bg-yellow-50 rounded-xl">
            <h4 class="flex items-center gap-2 font-semibold text-yellow-800">
                <span>⏳</span> Movimientos pendientes de autorización
            </h4>
            <div class="mt-2 space-y-2">
                @foreach($movimientosPendientes as $mov)
                <div class="flex items-center justify-between p-2 bg-white rounded-lg">
                    <div>
                        <p class="text-sm font-medium">{{ $mov->concepto }}</p>
                        <p class="text-xs text-gray-500">{{ $mov->categoria }} • {{ $mov->forma_pago }}</p>
                    </div>
                    <p class="font-bold text-yellow-600">${{ number_format($mov->monto, 2) }}</p>
                </div>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-yellow-600">Estos movimientos no afectan el saldo hasta ser autorizados.</p>
        </div>
        @endif

        {{-- Movimientos del día --}}
        <div>
            <h3 class="mb-4 text-lg font-bold text-slate-800">📋 Movimientos registrados</h3>
            <div class="space-y-2 overflow-y-auto max-h-96">
                @forelse($apertura->movimientos as $mov)
                <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $mov->tipo == 'ingreso' ? 'bg-green-100' : 'bg-red-100' }}">
                            <span class="text-lg">{{ $mov->tipo == 'ingreso' ? '💰' : '💸' }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $mov->concepto }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $mov->categoria }} • 
                                @php
                                    $iconoMov = '💰';
                                    $nombreMov = $mov->forma_pago;
                                    if(isset($formasPago) && $formasPago->count() > 0) {
                                        $formaPagoObj = $formasPago->firstWhere('clave', $mov->forma_pago);
                                        if($formaPagoObj && $formaPagoObj->icono) {
                                            $iconoMov = $formaPagoObj->icono;
                                            $nombreMov = $formaPagoObj->nombre;
                                        }
                                    }
                                @endphp
                                {!! $iconoMov !!} {{ $nombreMov }}
                                @if($mov->referencia) • Ref: {{ $mov->referencia }} @endif
                                @if($mov->requiere_autorizacion && !$mov->autorizado_por)
                                    <span class="ml-1 text-yellow-600">⏳ Pendiente</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold {{ $mov->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $mov->tipo == 'ingreso' ? '+' : '-' }} ${{ number_format($mov->monto, 2) }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $mov->created_at->format('H:i') }}</p>
                    </div>
                </div>
                @empty
                <p class="py-6 text-center text-gray-400">No hay movimientos registrados</p>
                @endforelse
            </div>
        </div>

        {{-- Footer del reporte --}}
        <div class="flex items-center justify-between pt-6 mt-8 border-t">
            <div class="text-xs text-gray-400">
                Generado el {{ now()->format('d/m/Y H:i:s') }}
            </div>
            <div class="flex gap-3 no-print">
                <button onclick="window.print()" class="px-4 py-2 text-white transition bg-gray-600 rounded-xl hover:bg-gray-700">
                    🖨️ Imprimir
                </button>
                <a href="{{ route('cajas.operaciones') }}" class="px-4 py-2 text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
                    ← Volver
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    header, aside, .sidebar, .header, .breadcrumbs, .no-print, button, a, .gap-3:has(button), .flex:has(button) {
        display: none !important;
    }
    body {
        background: white !important;
    }
    .max-w-5xl {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .bg-white {
        background: white !important;
        box-shadow: none !important;
    }
    .shadow-lg {
        box-shadow: none !important;
    }
}
</style>
@endsection