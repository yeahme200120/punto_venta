{{-- resources/views/cobranza/condonaciones.blade.php --}}
@extends('layouts.app')

@section('title', 'Condonaciones')
@section('page-title', 'Condonaciones')
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
        <span class="font-medium text-gray-700">Condonaciones</span>
    </li>
@endsection

@section('content')
<div class="space-y-5">
    <div class="p-4 border-l-4 border-yellow-400 bg-yellow-50 rounded-r-xl">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⚠️</span>
            <div>
                <p class="font-medium text-yellow-800">Condonación de adeudos</p>
                <p class="text-sm text-yellow-700">Aquí puedes condonar pagarés vencidos. Esta acción no se puede deshacer.</p>
            </div>
        </div>
    </div>

    @forelse($creditosVencidos as $credito)
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="p-4 border-b bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $credito->cliente->nombre }}</h3>
                    <p class="text-sm text-gray-500">Venta: {{ $credito->venta->folio }} | Crédito: ${{ number_format($credito->monto_total, 2) }}</p>
                </div>
                <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">Vencido</span>
            </div>
        </div>
        <div class="p-4">
            <div class="space-y-2">
                @foreach($credito->pagares as $pagare)
                <div class="flex items-center justify-between p-3 border rounded-xl {{ $pagare->fecha_vencimiento->isPast() ? 'bg-red-50' : '' }}">
                    <div>
                        <p class="font-mono text-sm font-bold">#{{ $pagare->folio }}</p>
                        <p class="text-xs text-gray-500">Vence: {{ $pagare->fecha_vencimiento->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-red-600">${{ number_format($pagare->monto, 2) }}</p>
                        @if($pagare->fecha_vencimiento->isPast() && $pagare->estado == 'pendiente')
                        <button type="button" 
                                onclick="condonarPagare({{ $pagare->id }}, '{{ $pagare->folio }}', {{ $pagare->monto }})"
                                class="px-3 py-1 text-sm text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                            Condonar
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @empty
    <div class="p-8 text-center text-gray-400 bg-white border shadow-sm rounded-2xl">
        No hay adeudos vencidos para condonar
    </div>
    @endforelse
</div>

<script>
    function condonarPagare(id, folio, monto) {
        Swal.fire({
            title: '⚠️ Condonar adeudo',
            html: `
                <div class="text-left">
                    <p class="mb-3">Pagaré: <strong>${folio}</strong></p>
                    <p class="mb-3">Monto a condonar: <strong class="text-red-600">$${monto.toFixed(2)}</strong></p>
                    <div class="mb-3">
                        <label class="block mb-1 text-sm font-medium">Motivo de la condonación *</label>
                        <textarea id="motivo" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Ej: Cliente con problemas financieros, Acuerdo comercial, etc."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="block mb-1 text-sm font-medium">Autorizado por *</label>
                        <input type="text" id="autorizadoPor" class="w-full px-3 py-2 border rounded-lg" placeholder="Nombre del autorizador">
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '✅ Confirmar condonación',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            preConfirm: () => {
                const motivo = document.getElementById('motivo').value;
                const autorizadoPor = document.getElementById('autorizadoPor').value;
                if (!motivo) {
                    Swal.showValidationMessage('Ingresa el motivo de la condonación');
                    return false;
                }
                if (!autorizadoPor) {
                    Swal.showValidationMessage('Ingresa quién autoriza la condonación');
                    return false;
                }
                return { motivo, autorizado_por: autorizadoPor };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('{{ route("cobranza.condonar") }}', {
                    pagare_id: id,
                    motivo: result.value.motivo,
                    autorizado_por: result.value.autorizado_por
                }).then(response => {
                    Swal.fire('Éxito', 'Adeudo condonado correctamente', 'success');
                    location.reload();
                }).catch(error => {
                    Swal.fire('Error', error.response?.data?.message || 'Error al condonar', 'error');
                });
            }
        });
    }
</script>
@endsection