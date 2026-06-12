@extends('layouts.app')

@section('title', 'Condonaciones')
@section('page-title', 'Condonaciones')

@section('content')
<div class="space-y-5">
    <div class="p-4 border-l-4 border-yellow-400 bg-yellow-50 rounded-r-xl">
        <p class="font-medium text-yellow-800">⚠️ Condonación de adeudos</p>
        <p class="text-sm text-yellow-700">Aquí puedes condonar pagarés vencidos. Esta acción no se puede deshacer.</p>
    </div>

    @forelse($creditosVencidos as $credito)
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="p-4 border-b bg-gray-50">
            <h3 class="font-semibold">{{ $credito->cliente->nombre }}</h3>
            <p class="text-sm text-gray-500">Venta: {{ $credito->venta->folio }} | ${{ number_format($credito->monto_total, 2) }}</p>
        </div>
        <div class="p-4 space-y-2">
            @foreach($credito->pagares->where('estado', 'pendiente')->where('fecha_vencimiento', '<', now()) as $pagare)
            <div class="flex items-center justify-between p-3 bg-red-50 border rounded-xl">
                <div>
                    <p class="font-mono font-bold">#{{ $pagare->folio }}</p>
                    <p class="text-xs">Vence: {{ $pagare->fecha_vencimiento->format('d/m/Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <p class="font-bold text-red-600">${{ number_format($pagare->monto, 2) }}</p>
                    @can('condonar_adeudo')
                    <button onclick="condonarPagare({{ $pagare->id }}, '{{ $pagare->folio }}', {{ $pagare->monto }})" class="px-3 py-1 text-sm text-white bg-orange-600 rounded-lg hover:bg-orange-700">Condonar</button>
                    @endcan
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="p-8 text-center text-gray-400 bg-white border shadow-sm rounded-2xl">No hay adeudos vencidos</div>
    @endforelse
</div>

<script>
if (typeof axios === 'undefined') { console.error('Axios no disponible'); }
axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
axios.defaults.headers.common['Accept'] = 'application/json';

async function condonarPagare(id, folio, monto) {
    @cannot('condonar_adeudo')
        Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return;
    @endcannot
    
    const { value: formValues } = await Swal.fire({
        title: '⚠️ Condonar adeudo',
        html: `<div class="text-left">
            <p>Pagaré: <strong>${folio}</strong></p>
            <p>Monto: <strong class="text-red-600">$${monto.toFixed(2)}</strong></p>
            <div class="mt-3"><label class="block mb-1 text-sm">Motivo *</label><textarea id="motivo" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea></div>
            <div class="mt-3"><label class="block mb-1 text-sm">Autorizado por *</label><input id="autorizado" class="w-full px-3 py-2 border rounded-lg"></div>
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '✅ Condonar',
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
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
    }
}
</script>
@endsection