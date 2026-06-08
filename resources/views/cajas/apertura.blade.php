@extends('layouts.app')

@section('title', 'Apertura de Caja')
@section('page-title', 'Apertura de Caja')
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
        <span class="font-medium text-gray-700">Apertura</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="max-w-3xl mx-auto">
    @if($aperturaActual)
    <div class="p-8 mb-6 text-center border border-green-200 bg-green-50 rounded-3xl">
        <div class="mb-4 text-5xl">💰</div>
        <h3 class="mb-2 text-xl font-bold text-green-800">Caja Abierta</h3>
        <p class="text-green-700">Tienes una caja abierta en este momento.</p>
        <div class="inline-block p-4 mt-4 bg-white rounded-xl">
            <p class="text-sm text-gray-500">Caja: <span class="font-semibold">{{ $aperturaActual->caja->nombre }}</span></p>
            <p class="text-sm text-gray-500">Apertura: <span class="font-semibold">{{ $aperturaActual->fecha_apertura->format('d/m/Y H:i') }}</span></p>
            <p class="text-sm text-gray-500">Monto inicial: <span class="font-semibold text-green-600">${{ number_format($aperturaActual->monto_inicial, 2) }}</span></p>
        </div>
        <div class="flex justify-center gap-3 mt-6">
            <a href="{{ route('cajas.operaciones') }}" class="px-6 py-2 text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
                Ir a operaciones
            </a>
            <button type="button" onclick="mostrarModalCierre({{ $aperturaActual->id }})" 
                    class="px-6 py-2 text-white transition bg-red-600 rounded-xl hover:bg-red-700">
                Cerrar caja
            </button>
        </div>
    </div>

    <div id="modalCierre" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50">
        <div class="w-full max-w-md p-6 bg-white rounded-2xl">
            <h3 class="mb-4 text-xl font-bold">Cerrar caja</h3>
            <form action="{{ route('cajas.cerrar') }}" method="POST">
                @csrf
                <input type="hidden" name="apertura_id" id="apertura_id">
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Monto final en caja *</label>
                    <input type="number" name="monto_final" step="0.01" required
                        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="w-full px-4 py-2 border rounded-xl"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="cerrarModal()" class="px-4 py-2 border rounded-xl hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-white bg-red-600 rounded-xl hover:bg-red-700">Cerrar caja</button>
                </div>
            </form>
        </div>
    </div>

    @elseif($aperturaAnterior)
    <div class="p-8 mb-6 text-center border border-yellow-200 bg-yellow-50 rounded-3xl">
        <div class="mb-4 text-5xl">⚠️</div>
        <h3 class="mb-2 text-xl font-bold text-yellow-800">Caja pendiente de cierre</h3>
        <p class="text-yellow-700">Tienes una caja abierta del día {{ $aperturaAnterior->fecha->format('d/m/Y') }}.</p>
        <p class="mt-2 text-yellow-700">Debes cerrarla antes de abrir una nueva.</p>
        <div class="inline-block p-4 mt-4 bg-white rounded-xl">
            <p class="text-sm text-gray-500">Caja: <span class="font-semibold">{{ $aperturaAnterior->caja->nombre }}</span></p>
            <p class="text-sm text-gray-500">Apertura: <span class="font-semibold">{{ $aperturaAnterior->fecha_apertura->format('d/m/Y H:i') }}</span></p>
        </div>
        <div class="mt-6">
            <button type="button" onclick="mostrarModalCierre({{ $aperturaAnterior->id }})" 
                    class="px-6 py-2 text-white transition bg-red-600 rounded-xl hover:bg-red-700">
                Cerrar caja pendiente
            </button>
        </div>
    </div>

    @else
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 text-3xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-green-500 to-emerald-500">💰</div>
            <h2 class="text-2xl font-bold text-slate-800">Abrir caja</h2>
            <p class="mt-2 text-gray-500">Selecciona la caja y el monto inicial</p>
        </div>

        <form action="{{ route('cajas.abrir') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Seleccionar caja *</label>
                    <select name="caja_id" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Seleccionar caja --</option>
                        @foreach($cajasDisponibles as $caja)
                        <option value="{{ $caja->id }}">{{ $caja->nombre }} ({{ $caja->codigo }}) - Saldo: ${{ number_format($caja->saldo_actual, 2) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Monto inicial (caja chica) *</label>
                    <div class="relative">
                        <span class="absolute text-gray-500 left-3 top-3">$</span>
                        <input type="number" name="monto_inicial" step="0.01" min="0" required
                            class="w-full py-3 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Monto en efectivo con el que se apertura la caja</p>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-green-600 to-emerald-500 rounded-xl hover:from-green-700 hover:to-emerald-600">
                    💰 Abrir caja
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

<script>
function mostrarModalCierre(aperturaId) {
    document.getElementById('apertura_id').value = aperturaId;
    document.getElementById('modalCierre').classList.remove('hidden');
    document.getElementById('modalCierre').classList.add('flex');
}

function cerrarModal() {
    document.getElementById('modalCierre').classList.add('hidden');
    document.getElementById('modalCierre').classList.remove('flex');
}
</script>
@endsection