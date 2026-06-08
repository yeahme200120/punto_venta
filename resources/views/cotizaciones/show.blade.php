@extends('layouts.app')

@section('title', 'Detalle de Cotización')
@section('page-title', 'Detalle de Cotización')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cotizaciones.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Cotizaciones
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">{{ $cotizacion->folio }}</span>
    </li>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="p-6 border-b bg-gradient-to-r from-indigo-600 to-cyan-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Cotización {{ $cotizacion->folio }}</h2>
                    <p class="text-indigo-100">{{ $cotizacion->fecha_cotizacion->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" 
                       target="_blank"
                       class="px-4 py-2 text-indigo-600 bg-white rounded-xl hover:bg-gray-100">
                        📄 Descargar PDF
                    </a>
                    <a href="{{ route('cotizaciones.index') }}" 
                       class="px-4 py-2 text-white bg-indigo-800 rounded-xl hover:bg-indigo-900">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">Cliente</p>
                    <p class="font-semibold">{{ $cotizacion->cliente->nombre ?? 'Cliente mostrador' }}</p>
                    @if($cotizacion->cliente)
                        <p class="text-xs text-gray-400">RFC: {{ $cotizacion->cliente->rfc ?? 'N/A' }}</p>
                    @endif
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">Atendió</p>
                    <p class="font-semibold">{{ $cotizacion->usuario->name }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">Válida hasta</p>
                    <p class="font-semibold {{ $cotizacion->fecha_validez && $cotizacion->fecha_validez->isPast() ? 'text-red-600' : 'text-green-600' }}">
                        {{ $cotizacion->fecha_validez ? $cotizacion->fecha_validez->format('d/m/Y') : 'No especificada' }}
                    </p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">Estado</p>
                    <p class="font-semibold">
                        @switch($cotizacion->estado)
                            @case('activa') 🟢 Activa @break
                            @case('convertida') 🔄 Convertida @break
                            @case('vencida') ⏰ Vencida @break
                            @case('cancelada') ❌ Cancelada @break
                            @default {{ $cotizacion->estado }}
                        @endswitch
                    </p>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-lg font-bold">Productos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Cantidad</th>
                                <th class="px-4 py-2 text-left">Producto</th>
                                <th class="px-4 py-2 text-right">Precio Unitario</th>
                                <th class="px-4 py-2 text-right">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($cotizacion->detalles as $detalle)
                            <tr>
                                <td class="px-4 py-2">{{ $detalle->cantidad }}</td>
                                <td class="px-4 py-2">{{ $detalle->producto->nombre }}</td>
                                <td class="px-4 py-2 text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="px-4 py-2 font-semibold text-right">${{ number_format($detalle->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-2 font-semibold text-right">Subtotal:</td>
                                <td class="px-4 py-2 text-right">${{ number_format($cotizacion->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-2 font-semibold text-right">IVA (16%):</td>
                                <td class="px-4 py-2 text-right">${{ number_format($cotizacion->iva, 2) }}</td>
                            </tr>
                            <tr class="border-t-2">
                                <td colspan="3" class="px-4 py-2 font-bold text-right">TOTAL:</td>
                                <td class="px-4 py-2 font-bold text-right text-indigo-600">${{ number_format($cotizacion->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($cotizacion->observaciones)
            <div class="p-4 bg-yellow-50 rounded-xl">
                <p class="text-sm text-gray-500">Observaciones:</p>
                <p>{{ $cotizacion->observaciones }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection