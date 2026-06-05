{{-- resources/views/cajas/reporte-dia.blade.php --}}
@extends('layouts.app')

@section('title', 'Reporte de Caja')
@section('page-title', 'Reporte de Caja')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cajas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
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
    {{-- Header del reporte --}}
    <div class="p-8 mb-6 bg-white shadow-lg rounded-3xl">
        <div class="mb-6 text-center">
            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 text-3xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">📊</div>
            <h2 class="text-2xl font-bold text-slate-800">Reporte de Caja</h2>
            <p class="text-gray-500">{{ $resumen['fecha'] }}</p>
            <p class="text-sm text-gray-400">Caja: {{ $apertura->caja->nombre }} ({{ $apertura->caja->codigo }})</p>
            <p class="text-sm text-gray-400">Usuario: {{ $apertura->usuario->name }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-4">
            <div class="p-4 text-center bg-green-50 rounded-xl">
                <p class="text-2xl font-bold text-green-600">${{ number_format($resumen['apertura'], 2) }}</p>
                <p class="text-xs text-gray-500">Apertura</p>
            </div>
            <div class="p-4 text-center bg-blue-50 rounded-xl">
                <p class="text-2xl font-bold text-blue-600">+ ${{ number_format($resumen['total_ingresos'], 2) }}</p>
                <p class="text-xs text-gray-500">Ingresos</p>
            </div>
            <div class="p-4 text-center bg-red-50 rounded-xl">
                <p class="text-2xl font-bold text-red-600">- ${{ number_format($resumen['total_egresos'], 2) }}</p>
                <p class="text-xs text-gray-500">Egresos</p>
            </div>
            <div class="p-4 text-center bg-indigo-50 rounded-xl">
                <p class="text-2xl font-bold text-indigo-600">${{ number_format($resumen['saldo_esperado'], 2) }}</p>
                <p class="text-xs text-gray-500">Saldo Esperado</p>
            </div>
        </div>

        {{-- Formas de pago --}}
        <div class="mb-8">
            <h3 class="mb-4 text-lg font-bold text-slate-800">💰 Desglose por forma de pago</h3>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                @foreach($resumen['por_forma_pago'] as $forma => $monto)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <span class="text-sm capitalize">
                        @switch($forma)
                            @case('efectivo') 💵 Efectivo @break
                            @case('tarjeta_debito') 💳 Tarjeta Débito @break
                            @case('tarjeta_credito') 💎 Tarjeta Crédito @break
                            @case('vale') 🎫 Vale @break
                            @case('transferencia') 🏦 Transferencia @break
                            @case('cheque') 📄 Cheque @break
                            @default {{ $forma }}
                        @endswitch
                    </span>
                    <span class="font-semibold">${{ number_format($monto, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

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
                                @switch($mov->forma_pago)
                                    @case('efectivo') 💵 Efectivo @break
                                    @case('tarjeta_debito') 💳 Tarjeta Débito @break
                                    @case('tarjeta_credito') 💎 Tarjeta Crédito @break
                                    @case('vale') 🎫 Vale @break
                                    @case('transferencia') 🏦 Transferencia @break
                                    @case('cheque') 📄 Cheque @break
                                    @default {{ $mov->forma_pago }}
                                @endswitch
                                @if($mov->referencia) • Ref: {{ $mov->referencia }} @endif
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
            <div class="flex gap-3">
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