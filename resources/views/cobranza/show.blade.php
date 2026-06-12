@extends('layouts.app')

@section('title', 'Detalle de Crédito')
@section('page-title', 'Crédito #' . $credito->id)

@section('content')
    {{-- ✅ SELECTOR DE CAJA (antes del formulario de abono) --}}
    @if(isset($cajasActivas) && $cajasActivas->count() > 1)
        <div class="p-3 mb-4 bg-white border border-indigo-200 rounded-xl">
            <label class="block mb-2 text-sm font-medium">🏦 Selecciona la caja para registrar:</label>
            <select id="cajaActivaSelect" class="w-full px-3 py-2 border rounded-lg">
                @foreach($cajasActivas as $caja)
                    <option value="{{ $caja->id }}">
                        🏦 {{ $caja->caja->nombre }} | 👤 {{ $caja->usuario->name }} | 💰
                        ${{ number_format($caja->monto_inicial + $caja->total_ingresos - $caja->total_egresos, 2) }}
                    </option>
                @endforeach
            </select>
        </div>
    @elseif(isset($cajaAbierta) && $cajaAbierta)
        <div class="p-3 mb-4 bg-green-50 border border-green-200 rounded-xl">
            <p class="text-sm text-green-700">
                🏦 <strong>{{ $cajaAbierta->caja->nombre }}</strong> |
                👤 {{ $cajaAbierta->usuario->name }} |
                💰
                ${{ number_format($cajaAbierta->monto_inicial + $cajaAbierta->total_ingresos - $cajaAbierta->total_egresos, 2) }}
            </p>
        </div>
    @endif
    <div class="space-y-5">
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
            <div class="p-5 border-b bg-gradient-to-r from-indigo-50 to-cyan-50">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Crédito #{{ $credito->id }}</h2>
                        <p class="text-sm text-gray-500">Venta: {{ $credito->venta->folio }} | Cliente:
                            {{ $credito->cliente->nombre }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @can('ver_cobranza')
                            <a href="{{ route('ventas.pagares', $credito->id) }}" target="_blank"
                                class="px-4 py-2 text-indigo-600 border rounded-lg hover:bg-indigo-50">📄 Pagarés</a>
                        @endcan
                        <a href="{{ route('cobranza.index') }}"
                            class="px-4 py-2 text-white bg-gray-600 rounded-lg hover:bg-gray-700">← Volver</a>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-xl font-bold text-indigo-600">${{ number_format($credito->monto_total, 2) }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500">Pagado</p>
                        <p class="text-xl font-bold text-green-600">${{ number_format($credito->monto_pagado, 2) }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500">Saldo</p>
                        <p
                            class="text-xl font-bold {{ $credito->saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                            ${{ number_format($credito->saldo_pendiente, 2) }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500">Estado</p>
                        <p class="text-xl font-bold">
                            {{ $credito->estado == 'pagado' ? '✅ Pagado' : ($credito->estado == 'vencido' ? '⏰ Vencido' : '🟢 Activo') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-6 lg:flex-row">
                    {{-- Pagarés --}}
                    <div class="flex-1">
                        <h3 class="mb-3 text-lg font-semibold">📜 Pagarés</h3>
                        <div class="space-y-2">
                            @forelse($credito->pagares as $pagare)
                                @php $isVencido = $pagare->fecha_vencimiento->isPast() && $pagare->estado == 'pendiente'; @endphp
                                <div
                                    class="p-3 border rounded-xl {{ $pagare->estado == 'pagado' ? 'bg-green-50 border-green-200' : ($isVencido ? 'bg-red-50 border-red-200' : 'bg-white') }}">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-mono font-bold">#{{ $pagare->folio }}</span>
                                            <span class="ml-2 text-xs">Vence:
                                                {{ $pagare->fecha_vencimiento->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold">${{ number_format($pagare->monto, 2) }}</span>
                                            @can('pagar_pagare')
                                                @if($pagare->estado == 'pendiente')
                                                    <button
                                                        onclick="pagarPagare({{ $pagare->id }}, '{{ $pagare->folio }}', {{ $pagare->monto }})"
                                                        class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Pagar</button>
                                                @endif
                                            @endcan
                                            @can('condonar_adeudo')
                                                @if($isVencido)
                                                    <button
                                                        onclick="condonarPagare({{ $pagare->id }}, '{{ $pagare->folio }}', {{ $pagare->monto }})"
                                                        class="px-3 py-1 text-sm text-white bg-orange-600 rounded-lg hover:bg-orange-700">Condonar</button>
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="py-6 text-center text-gray-400">No hay pagarés</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Formulario de abono --}}
                    @can('registrar_abono')
                        <div class="w-full lg:w-96">
                            <div class="sticky p-4 bg-gray-50 rounded-xl top-24">
                                <h3 class="mb-3 text-lg font-semibold">💰 Registrar abono</h3>
                                <form id="formAbono" onsubmit="return registrarAbono(event)">
                                    @csrf
                                    <input type="hidden" name="credito_id" value="{{ $credito->id }}">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block mb-1 text-sm font-medium">Monto *</label>
                                            <input type="number" name="monto" id="montoAbono" step="0.01" min="0.01" required
                                                class="w-full px-3 py-2 border rounded-lg"
                                                value="{{ $credito->pagares->where('estado', 'pendiente')->first()->monto ?? $credito->saldo_pendiente }}">
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-sm font-medium">Forma de pago *</label>
                                            <select name="forma_pago" required class="w-full px-3 py-2 border rounded-lg">
                                                @foreach($formasPago as $forma)
                                                    <option value="{{ $forma->clave }}">{{ $forma->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-sm font-medium">Referencia</label>
                                            <input type="text" name="referencia" class="w-full px-3 py-2 border rounded-lg">
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-sm font-medium">Observaciones</label>
                                            <textarea name="observaciones" rows="2"
                                                class="w-full px-3 py-2 border rounded-lg"></textarea>
                                        </div>
                                        <button type="submit"
                                            class="w-full py-2 text-white bg-green-600 rounded-lg hover:bg-green-700">💰
                                            Registrar abono</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan
                </div>

                {{-- Historial de pagos --}}
                <div class="mt-6">
                    <h3 class="mb-3 text-lg font-semibold">📋 Historial de pagos</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-xs text-left">Fecha</th>
                                    <th class="px-4 py-2 text-xs text-right">Monto</th>
                                    <th class="px-4 py-2 text-xs text-center">Tipo</th>
                                    <th class="px-4 py-2 text-xs text-left">Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($credito->cobranzas as $cobranza)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm">{{ $cobranza->fecha_cobro->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2 font-semibold text-right text-green-600">
                                            +${{ number_format($cobranza->monto, 2) }}</td>
                                        <td class="px-4 py-2 text-center"><span
                                                class="px-2 py-0.5 text-xs rounded-full {{ $cobranza->tipo == 'abono' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">{{ $cobranza->tipo == 'abono' ? 'Abono' : 'Pago' }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-sm">{{ $cobranza->usuario->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">No hay pagos registrados
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
        if (typeof axios === 'undefined') { console.error('Axios no disponible'); }
        axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
        axios.defaults.headers.common['Accept'] = 'application/json';

        async function registrarAbono(event) {
            event.preventDefault();
            const form = document.getElementById('formAbono');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // ✅ Agregar caja seleccionada
            const cajaSelect = document.getElementById('cajaActivaSelect');
            if (cajaSelect) data.caja_apertura_id = cajaSelect.value;

            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await axios.post('{{ route("cobranza.abono.store") }}', data);
                if (res.data?.success) { await Swal.fire({ icon: 'success', title: 'Abono registrado', timer: 2000 }); location.reload(); }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
            return false;
        }

        async function pagarPagare(id, folio, monto) {
            @cannot('pagar_pagare')
            Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return;
            @endcannot

            const { value: formValues } = await Swal.fire({
                title: 'Pagar pagaré',
                html: `<div class="text-left">
                            <p>Pagaré: <strong>${folio}</strong></p>
                            {{-- ✅ MONTO FIJO, NO MODIFICABLE --}}
                            <p class="text-lg font-bold text-indigo-600">Monto: $${monto.toFixed(2)}</p>
                            <p class="text-xs text-gray-400">El monto del pagaré no es modificable</p>
                            <div class="mt-3">
                                <label class="block mb-1 text-sm">Forma de pago</label>
                                <select id="fp" class="w-full px-3 py-2 border rounded-lg">
                                    @foreach($formasPago as $f)<option value="{{ $f->clave }}">{{ $f->nombre }}</option>@endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <label class="block mb-1 text-sm">Referencia</label>
                                <input id="ref" class="w-full px-3 py-2 border rounded-lg">
                            </div>
                            <div class="mt-3">
                                <label class="block mb-1 text-sm">Observaciones</label>
                                <textarea id="obs" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                            </div>
                        </div>`,
                showCancelButton: true,
                confirmButtonText: '✅ Pagar $' + monto.toFixed(2),
                cancelButtonText: 'Cancelar',
                preConfirm: () => ({
                    forma_pago: document.getElementById('fp').value,
                    referencia: document.getElementById('ref').value,
                    observaciones: document.getElementById('obs').value,
                    caja_apertura_id: document.getElementById('cajaActivaSelect')?.value || null
                })
            });

            if (!formValues) return;
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await axios.post(`/cobranza/pagare/${id}/pagar`, formValues);
                if (res.data?.success) { await Swal.fire({ icon: 'success', title: '¡Pagado!', timer: 2000 }); location.reload(); }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
        }

        async function condonarPagare(id, folio, monto) {
            @cannot('condonar_adeudo')
            Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return;
            @endcannot

            const { value: formValues } = await Swal.fire({
                title: '⚠️ Condonar adeudo',
                html: `<div class="text-left">
                            <p>Pagaré: <strong>${folio}</strong></p>
                            <p>Monto a condonar: <strong class="text-red-600">$${monto.toFixed(2)}</strong></p>
                            <div class="mt-3"><label class="block mb-1 text-sm">Motivo *</label><textarea id="motivo" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Motivo de la condonación"></textarea></div>
                            <div class="mt-3"><label class="block mb-1 text-sm">Autorizado por *</label><input id="autorizado" class="w-full px-3 py-2 border rounded-lg" placeholder="Nombre del autorizador"></div>
                        </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '✅ Condonar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444',
                preConfirm: () => {
                    if (!document.getElementById('motivo').value) { Swal.showValidationMessage('Ingresa el motivo'); return false; }
                    if (!document.getElementById('autorizado').value) { Swal.showValidationMessage('Ingresa quién autoriza'); return false; }
                    return { motivo: document.getElementById('motivo').value, autorizado_por: document.getElementById('autorizado').value, pagare_id: id };
                }
            });

            if (!formValues) return;
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await axios.post('{{ route("cobranza.condonar") }}', formValues);
                if (res.data?.success) { await Swal.fire({ icon: 'success', title: 'Condonado', timer: 2000 }); location.reload(); }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
        }
    </script>
@endsection