{{-- resources/views/cajas/transferencias.blade.php --}}
@extends('layouts.app')

@section('title', 'Transferencias entre Cajas')
@section('page-title', 'Transferencias entre Cajas')
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
        <span class="font-medium text-gray-700">Transferencias</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="max-w-3xl mx-auto">
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 text-3xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-blue-500 to-cyan-500">🔄</div>
            <h2 class="text-2xl font-bold text-slate-800">Transferir entre cajas</h2>
            <p class="mt-2 text-gray-500">Solicita una transferencia de fondos entre cajas</p>
        </div>

        <form action="{{ route('cajas.transferencia.solicitar') }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Caja de origen *</label>
                    <select name="caja_origen_id" id="caja_origen_id" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar caja origen</option>
                        @foreach($cajas as $caja)
                        <option value="{{ $caja->id }}">{{ $caja->nombre }} ({{ $caja->codigo }}) - ${{ number_format($caja->saldo_actual, 2) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Caja de destino *</label>
                    <select name="caja_destino_id" id="caja_destino_id" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar caja destino</option>
                        @foreach($cajas as $caja)
                        <option value="{{ $caja->id }}">{{ $caja->nombre }} ({{ $caja->codigo }}) - ${{ number_format($caja->saldo_actual, 2) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Monto a transferir *</label>
                <div class="relative">
                    <span class="absolute text-gray-500 left-3 top-3">$</span>
                    <input type="number" name="monto" id="monto" step="0.01" min="0.01" required
                        class="w-full py-3 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>
                <p id="saldo-disponible" class="mt-1 text-xs text-gray-500"></p>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Motivo de la transferencia *</label>
                <textarea name="motivo" rows="3" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500" placeholder="Ej: Traspaso de fondos para cambio de turno, Ajuste de saldos, etc."></textarea>
            </div>

            <div class="p-4 border border-yellow-200 bg-yellow-50 rounded-xl">
                <p class="flex items-center gap-2 text-sm text-yellow-800">
                    <span>⚠️</span>
                    Las transferencias requieren autorización de un administrador. Una vez autorizada, los fondos serán transferidos automáticamente.
                </p>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t">
                <a href="{{ route('cajas.cajas.index') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-blue-600 to-cyan-500 rounded-xl hover:from-blue-700 hover:to-cyan-600">
                    🔄 Solicitar transferencia
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const cajas = @json($cajas);

function actualizarSaldoDisponible() {
    const origenId = document.getElementById('caja_origen_id').value;
    const montoInput = document.getElementById('monto');
    const saldoSpan = document.getElementById('saldo-disponible');
    
    if (origenId) {
        const caja = cajas.find(c => c.id == origenId);
        if (caja) {
            saldoSpan.innerHTML = `Saldo disponible en caja origen: <strong>$${formatNumber(caja.saldo_actual)}</strong>`;
            montoInput.max = caja.saldo_actual;
            montoInput.placeholder = `Máximo $${formatNumber(caja.saldo_actual)}`;
        }
    } else {
        saldoSpan.innerHTML = '';
        montoInput.max = '';
        montoInput.placeholder = 'Monto a transferir';
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
}

document.getElementById('caja_origen_id').addEventListener('change', actualizarSaldoDisponible);
document.getElementById('caja_destino_id').addEventListener('change', function() {
    const origen = document.getElementById('caja_origen_id').value;
    const destino = this.value;
    
    if (origen === destino) {
        Swal.fire({
            icon: 'warning',
            title: 'Cajas iguales',
            text: 'No puedes transferir fondos a la misma caja',
            confirmButtonColor: '#4f46e5'
        });
        this.value = '';
    }
});
</script>
@endsection