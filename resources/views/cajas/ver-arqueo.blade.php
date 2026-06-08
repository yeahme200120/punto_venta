@extends('layouts.app')

@section('title', 'Detalle de Arqueo')
@section('page-title', 'Detalle de Arqueo')
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
        <a href="{{ route('cajas.arqueos') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Arqueos
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Ver Detalle</span>
    </li>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="p-6 bg-white shadow-lg rounded-3xl">
        <div class="mb-6 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">📊</div>
            <h2 class="text-2xl font-bold text-slate-800">Detalle de Arqueo</h2>
            <p class="text-gray-500">{{ $arqueo->created_at->format('d/m/Y H:i:s') }}</p>
            <p class="text-sm text-gray-400">Usuario: {{ $arqueo->usuario->name }}</p>
        </div>
        
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">💵 Efectivo contado</p>
                    <p class="text-xl font-bold text-green-600">${{ number_format($arqueo->efectivo_contado, 2) }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">💳 Tarjeta Débito</p>
                    <p class="text-xl font-bold text-blue-600">${{ number_format($arqueo->tarjeta_debito_contado, 2) }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">💎 Tarjeta Crédito</p>
                    <p class="text-xl font-bold text-purple-600">${{ number_format($arqueo->tarjeta_credito_contado, 2) }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">🎫 Vale</p>
                    <p class="text-xl font-bold text-orange-600">${{ number_format($arqueo->vale_contado, 2) }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">🏦 Transferencia</p>
                    <p class="text-xl font-bold text-cyan-600">${{ number_format($arqueo->transferencia_contado, 2) }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">📄 Cheque</p>
                    <p class="text-xl font-bold text-gray-600">${{ number_format($arqueo->cheque_contado, 2) }}</p>
                </div>
            </div>
            
            <div class="pt-4 mt-4 border-t">
                <div class="flex justify-between p-3 bg-blue-50 rounded-xl">
                    <span class="font-semibold">💰 Total contado:</span>
                    <span class="text-xl font-bold text-green-600">${{ number_format($arqueo->total_contado, 2) }}</span>
                </div>
                <div class="flex justify-between p-3 bg-indigo-50 rounded-xl">
                    <span class="font-semibold">📊 Total según sistema:</span>
                    <span class="text-xl font-bold text-indigo-600">${{ number_format($arqueo->total_sistema, 2) }}</span>
                </div>
                <div class="flex justify-between p-3 {{ $arqueo->diferencia >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-xl">
                    <span class="font-semibold">⚖️ Diferencia:</span>
                    <span class="text-xl font-bold {{ $arqueo->diferencia >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $arqueo->diferencia >= 0 ? '+' : '' }}${{ number_format($arqueo->diferencia, 2) }}
                        @if($arqueo->diferencia > 0)
                            <span class="text-sm">(Sobrante)</span>
                        @elseif($arqueo->diferencia < 0)
                            <span class="text-sm">(Faltante)</span>
                        @else
                            <span class="text-sm">(Cuadrado)</span>
                        @endif
                    </span>
                </div>
            </div>
            
            @if($arqueo->observaciones)
            <div class="p-3 bg-yellow-50 rounded-xl">
                <p class="text-sm text-gray-500">📝 Observaciones:</p>
                <p class="mt-1">{{ $arqueo->observaciones }}</p>
            </div>
            @endif
        </div>
        
        <div class="flex justify-end gap-3 pt-4 mt-6 border-t">
            <button onclick="window.print()" class="px-4 py-2 text-white transition bg-gray-600 rounded-xl hover:bg-gray-700">
                🖨️ Imprimir
            </button>
            <a href="{{ route('cajas.arqueos') }}" class="px-4 py-2 text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
                ← Volver
            </a>
        </div>
    </div>
</div>

<style>
@media print {
    header, aside, .sidebar, .header, .breadcrumbs, .no-print, button, a {
        display: none !important;
    }
    body {
        background: white !important;
    }
    .max-w-3xl {
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
    .rounded-3xl, .rounded-xl {
        border-radius: 0 !important;
    }
}
</style>
@endsection