@extends('layouts.app')

@section('title', 'Detalles de Apertura')
@section('page-title', 'Detalles de Apertura')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="overflow-hidden bg-white shadow-lg rounded-2xl">
        <div class="p-6 border-b bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $apertura->caja->nombre }}</h3>
                    <p class="text-gray-500">Apertura #{{ $apertura->id }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $apertura->estado == 'abierta' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $apertura->estado == 'abierta' ? '🟢 ABIERTA' : '🔴 CERRADA' }}
                </span>
            </div>
        </div>
        
        <div class="grid gap-6 p-6 md:grid-cols-2">
            <div>
                <h4 class="mb-3 font-semibold text-gray-700">Información de Apertura</h4>
                <div class="space-y-2">
                    <p><span class="text-gray-500">Usuario:</span> {{ $apertura->usuario->name }}</p>
                    <p><span class="text-gray-500">Sucursal:</span> {{ $apertura->sucursal->nombre ?? 'N/A' }}</p>
                    <p><span class="text-gray-500">Fecha apertura:</span> {{ $apertura->fecha_apertura->format('d/m/Y H:i') }}</p>
                    @if($apertura->fecha_cierre)
                    <p><span class="text-gray-500">Fecha cierre:</span> {{ $apertura->fecha_cierre->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
            
            <div>
                <h4 class="mb-3 font-semibold text-gray-700">Montos</h4>
                <div class="space-y-2">
                    <p><span class="text-gray-500">Monto inicial:</span> <span class="font-semibold text-green-600">${{ number_format($apertura->monto_inicial, 2) }}</span></p>
                    <p><span class="text-gray-500">Total ingresos:</span> <span class="font-semibold text-green-600">+${{ number_format($apertura->total_ingresos, 2) }}</span></p>
                    <p><span class="text-gray-500">Total egresos:</span> <span class="font-semibold text-red-600">-${{ number_format($apertura->total_egresos, 2) }}</span></p>
                    <p><span class="text-gray-500">Saldo esperado:</span> <span class="font-bold text-blue-600">${{ number_format($apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos, 2) }}</span></p>
                    @if($apertura->monto_final)
                    <p><span class="text-gray-500">Monto final:</span> <span class="font-bold">${{ number_format($apertura->monto_final, 2) }}</span></p>
                    @endif
                </div>
            </div>
        </div>
        
        @if($apertura->movimientos->count() > 0)
        <div class="p-6 border-t">
            <h4 class="mb-3 font-semibold text-gray-700">Últimos movimientos</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Fecha</th>
                            <th class="px-4 py-2 text-left">Tipo</th>
                            <th class="px-4 py-2 text-left">Concepto</th>
                            <th class="px-4 py-2 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apertura->movimientos as $mov)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs {{ $mov->tipo == 'ingreso' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $mov->tipo == 'ingreso' ? '💰 Ingreso' : '💸 Egreso' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $mov->concepto }}</td>
                            <td class="px-4 py-2 text-right {{ $mov->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mov->tipo == 'ingreso' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
        <div class="p-6 border-t bg-gray-50">
            <a href="{{ route('cajas.apertura') }}" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300">
                ← Volver
            </a>
        </div>
    </div>
</div>
@endsection