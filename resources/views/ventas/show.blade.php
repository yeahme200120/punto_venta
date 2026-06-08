@extends('layouts.app')

@section('title', 'Detalle de Venta')
@section('page-title', 'Detalle de Venta')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
        <div class="p-5 border-b bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Venta {{ $venta->folio }}</h2>
                    <p class="text-sm text-gray-500">{{ $venta->fecha_venta->format('d/m/Y H:i:s') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('ventas.ticket', $venta) }}" target="_blank" class="px-4 py-2 text-indigo-600 border rounded-lg hover:bg-indigo-50">
                        🧾 Ticket
                    </a>
                    <a href="{{ route('ventas.historial') }}" class="px-4 py-2 text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Atendió</p>
                    <p class="font-semibold">{{ $venta->usuario->name }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Cliente</p>
                    <p class="font-semibold">{{ $venta->cliente->nombre ?? 'Cliente mostrador' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Tipo de venta</p>
                    <p class="font-semibold">{{ ucfirst($venta->tipo) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Estado</p>
                    <p class="font-semibold">{{ ucfirst($venta->estado) }}</p>
                </div>
            </div>

            <div>
                <h3 class="mb-3 font-semibold text-gray-800">Productos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-xs text-left">Producto</th>
                                <th class="px-4 py-2 text-xs text-center">Cantidad</th>
                                <th class="px-4 py-2 text-xs text-right">Precio</th>
                                <th class="px-4 py-2 text-xs text-right">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($venta->detalles as $detalle)
                            <tr>
                                <td class="px-4 py-2">{{ $detalle->producto->nombre }}</td>
                                <td class="px-4 py-2 text-center">{{ $detalle->cantidad }}</td>
                                <td class="px-4 py-2 text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="px-4 py-2 font-semibold text-right">${{ number_format($detalle->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-2 font-semibold text-right">Subtotal:</td>
                                <td class="px-4 py-2 text-right">${{ number_format($venta->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-2 font-semibold text-right">IVA (16%):</td>
                                <td class="px-4 py-2 text-right">${{ number_format($venta->iva, 2) }}</td>
                            </tr>
                            <tr class="border-t">
                                <td colspan="3" class="px-4 py-2 font-bold text-right">TOTAL:</td>
                                <td class="px-4 py-2 font-bold text-right text-indigo-600">${{ number_format($venta->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection