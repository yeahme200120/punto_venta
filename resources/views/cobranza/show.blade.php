{{-- resources/views/cobranza/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle de Crédito')
@section('page-title', 'Detalle de Crédito')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cobranza.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Cobranza
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Crédito #{{ $credito->id }}</span>
    </li>
@endsection

@section('content')
<div class="space-y-5">
    {{-- Información del crédito --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="p-5 border-b bg-gradient-to-r from-indigo-50 to-cyan-50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full">
                        <span class="text-2xl">📋</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Crédito #{{ $credito->id }}</h2>
                        <p class="text-sm text-gray-500">Venta: {{ $credito->venta->folio }} | Cliente: {{ $credito->cliente->nombre }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('ventas.pagares', $credito->id) }}" target="_blank" 
                       class="px-4 py-2 text-indigo-600 border rounded-lg hover:bg-indigo-50">
                        📄 Imprimir pagarés
                    </a>
                    <a href="{{ route('cobranza.index') }}" 
                       class="px-4 py-2 text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Total del crédito</p>
                    <p class="text-xl font-bold text-indigo-600">${{ number_format($credito->monto_total, 2) }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Monto pagado</p>
                    <p class="text-xl font-bold text-green-600">${{ number_format($credito->monto_pagado, 2) }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Saldo pendiente</p>
                    <p class="text-xl font-bold {{ $credito->saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                        ${{ number_format($credito->saldo_pendiente, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-500">Estado</p>
                    <p class="text-xl font-bold">
                        @if($credito->estado == 'pagado')
                            <span class="text-green-600">✅ Pagado</span>
                        @elseif($credito->estado == 'vencido')
                            <span class="text-red-600">⏰ Vencido</span>
                        @else
                            <span class="text-blue-600">🟢 Activo</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                {{-- Pagarés --}}
                <div class="flex-1">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">📜 Pagarés</h3>
                    <div class="space-y-2">
                        @forelse($credito->pagares as $pagare)
                            @php
                                $isVencido = $pagare->fecha_vencimiento->isPast() && $pagare->estado == 'pendiente';
                                $isPagado = $pagare->estado == 'pagado';
                            @endphp
                            <div class="p-3 border rounded-xl {{ $isVencido ? 'bg-red-50 border-red-200' : ($isPagado ? 'bg-green-50 border-green-200' : 'bg-white') }}">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-sm font-bold">#{{ $pagare->folio }}</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full 
                                                {{ $isPagado ? 'bg-green-100 text-green-700' : ($isVencido ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ $isPagado ? 'Pagado' : ($isVencido ? 'Vencido' : 'Pendiente') }}
                                            </span>
                                        </div>
                                        <div class="flex gap-3 mt-1 text-xs text-gray-500">
                                            <span>Pago #{{ $pagare->numero_pago }}</span>
                                            <span>Vence: {{ $pagare->fecha_vencimiento->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold ${{ $isPagado ? 'text-green-600' : 'text-indigo-600' }}">
                                            ${{ number_format($pagare->monto, 2) }}
                                        </p>
                                        @if(!$isPagado && $credito->estado != 'pagado')
                                        <button type="button" 
                                                onclick="pagarPagare({{ $pagare->id }}, '{{ $pagare->folio }}', {{ $pagare->monto }})"
                                                class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                            Pagar
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-gray-400">No hay pagarés registrados</p>
                        @endforelse
                    </div>
                </div>

                {{-- Formulario de abono --}}
                <div class="w-full lg:w-96">
                    <div class="sticky top-24">
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <h3 class="mb-3 text-lg font-semibold text-gray-800">💰 Registrar abono</h3>
                            <form action="{{ route('cobranza.abono.store') }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="hidden" name="credito_id" value="{{ $credito->id }}">
                                
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Monto sugerido</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                        <input type="number" id="montoSugerido" class="w-full py-2 pl-8 pr-4 bg-gray-100 border rounded-lg" 
                                               value="{{ number_format($credito->pagares->where('estado', 'pendiente')->first()->monto ?? $credito->saldo_pendiente, 2) }}" readonly>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">Sugerido: próximo pagaré</p>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Monto a pagar *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                        <input type="number" name="monto" id="montoAbono" step="0.01" min="0.01" required
                                            class="w-full py-2 pl-8 pr-4 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            value="{{ $credito->pagares->where('estado', 'pendiente')->first()->monto ?? $credito->saldo_pendiente }}">
                                    </div>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Forma de pago *</label>
                                    <select name="forma_pago" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        @foreach($formasPago as $forma)
                                            <option value="{{ $forma->clave }}">{!! $forma->icono !!} {{ $forma->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Referencia</label>
                                    <input type="text" name="referencia" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Observaciones</label>
                                    <textarea name="observaciones" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                                </div>

                                <button type="submit" class="w-full py-2 text-white transition bg-green-600 rounded-lg hover:bg-green-700">
                                    💰 Registrar abono
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Historial de pagos --}}
            <div class="mt-6">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">📋 Historial de pagos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-xs text-left">Fecha</th>
                                <th class="px-4 py-2 text-xs text-right">Monto</th>
                                <th class="px-4 py-2 text-xs text-center">Tipo</th>
                                <th class="px-4 py-2 text-xs text-left">Usuario</th>
                                <th class="px-4 py-2 text-xs text-left">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($credito->cobranzas as $cobranza)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm">{{ $cobranza->fecha_cobro->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 font-semibold text-right text-green-600">+${{ number_format($cobranza->monto, 2) }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($cobranza->tipo == 'abono')
                                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">Abono</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">Pago de pagaré</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $cobranza->usuario->name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $cobranza->observaciones ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    No hay pagos registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const montoSugerido = parseFloat(document.getElementById('montoSugerido')?.value.replace(/,/g, '') || 0);
    
    document.getElementById('montoAbono')?.addEventListener('input', function() {
        if (parseFloat(this.value) > {{ $credito->saldo_pendiente }}) {
            this.value = {{ $credito->saldo_pendiente }};
        }
    });

    function pagarPagare(id, folio, monto) {
        Swal.fire({
            title: 'Pagar pagaré',
            html: `
                <div class="text-left">
                    <p class="mb-3">Pagaré: <strong>${folio}</strong></p>
                    <p class="mb-3">Monto: <strong>$${monto.toFixed(2)}</strong></p>
                    <div class="mb-3">
                        <label class="block mb-1 text-sm font-medium">Forma de pago *</label>
                        <select id="formaPago" class="w-full px-3 py-2 border rounded-lg">
                            @foreach($formasPago as $forma)
                                <option value="{{ $forma->clave }}">{!! $forma->icono !!} {{ $forma->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block mb-1 text-sm font-medium">Referencia</label>
                        <input type="text" id="referencia" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-3">
                        <label class="block mb-1 text-sm font-medium">Observaciones</label>
                        <textarea id="observaciones" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '✅ Pagar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                return {
                    forma_pago: document.getElementById('formaPago').value,
                    referencia: document.getElementById('referencia').value,
                    observaciones: document.getElementById('observaciones').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/cobranza/pagare/${id}/pagar`, result.value)
                    .then(response => {
                        Swal.fire('Éxito', 'Pagaré pagado correctamente', 'success');
                        location.reload();
                    })
                    .catch(error => {
                        Swal.fire('Error', error.response?.data?.message || 'Error al pagar', 'error');
                    });
            }
        });
    }
</script>
@endsection