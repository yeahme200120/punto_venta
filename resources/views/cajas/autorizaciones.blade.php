{{-- resources/views/cajas/autorizaciones.blade.php --}}
@extends('layouts.app')

@section('title', 'Autorizaciones Pendientes')
@section('page-title', 'Autorizaciones Pendientes')
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
        <span class="font-medium text-gray-700">Autorizaciones</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="space-y-6">
    {{-- Movimientos pendientes --}}
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="p-6 border-b bg-yellow-50">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 text-white bg-yellow-500 rounded-full">⏳</div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Movimientos pendientes de autorización</h2>
                    <p class="text-sm text-gray-500">Movimientos que requieren autorización de un administrador</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Concepto</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Monto</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($movimientosPendientes as $movimiento)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-8 h-8 font-bold text-indigo-600 bg-indigo-100 rounded-full">
                                    {{ strtoupper(substr($movimiento->usuario->name, 0, 1)) }}
                                </div>
                                <span class="text-sm">{{ $movimiento->usuario->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium">{{ $movimiento->concepto }}</p>
                                <p class="text-xs text-gray-500">{{ $movimiento->categoria }} • {{ $movimiento->forma_pago }}</p>
                                @if($movimiento->referencia)
                                <p class="text-xs text-gray-400">Ref: {{ $movimiento->referencia }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($movimiento->tipo == 'ingreso')
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">💰 Ingreso</span>
                            @else
                                <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">💸 Egreso</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-bold {{ $movimiento->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movimiento->tipo == 'ingreso' ? '+' : '-' }} ${{ number_format($movimiento->monto, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" onclick="mostrarModalAutorizacion('movimiento', {{ $movimiento->id }})" 
                                    class="px-4 py-2 text-sm text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                Autorizar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            No hay movimientos pendientes de autorización
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movimientosPendientes->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $movimientosPendientes->links() }}
        </div>
        @endif
    </div>

    {{-- Transferencias pendientes --}}
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="p-6 border-b bg-blue-50">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 text-white bg-blue-500 rounded-full">🔄</div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Transferencias pendientes de autorización</h2>
                    <p class="text-sm text-gray-500">Transferencias entre cajas que requieren autorización</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Solicitante</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Origen → Destino</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Motivo</th>
                        <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Monto</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($transferenciasPendientes as $transferencia)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $transferencia->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-8 h-8 font-bold text-indigo-600 bg-indigo-100 rounded-full">
                                    {{ strtoupper(substr($transferencia->usuario->name, 0, 1)) }}
                                </div>
                                <span class="text-sm">{{ $transferencia->usuario->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm">{{ $transferencia->cajaOrigen->codigo }}</span>
                                <span>→</span>
                                <span class="font-mono text-sm">{{ $transferencia->cajaDestino->codigo }}</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ $transferencia->cajaOrigen->nombre }} → {{ $transferencia->cajaDestino->nombre }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ Str::limit($transferencia->motivo, 50) }}</td>
                        <td class="px-6 py-4 font-bold text-right text-orange-600">
                            ${{ number_format($transferencia->monto, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" onclick="mostrarModalAutorizacion('transferencia', {{ $transferencia->id }})" 
                                    class="px-4 py-2 text-sm text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                Autorizar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            No hay transferencias pendientes de autorización
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transferenciasPendientes->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $transferenciasPendientes->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal de autorización --}}
<div id="modalAutorizacion" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50">
    <div class="w-full max-w-md p-6 bg-white rounded-2xl">
        <h3 class="mb-4 text-xl font-bold">Autorizar</h3>
        <p id="modalMensaje" class="mb-4 text-gray-600"></p>
        <form id="formAutorizacion" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium">Contraseña maestra *</label>
                <input type="password" name="password_maestra" required 
                    class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Ingresa tu contraseña maestra para autorizar esta operación</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="cerrarModal()" class="px-4 py-2 border rounded-xl hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Autorizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function mostrarModalAutorizacion(tipo, id) {
    const modal = document.getElementById('modalAutorizacion');
    const form = document.getElementById('formAutorizacion');
    const mensaje = document.getElementById('modalMensaje');
    
    if (tipo === 'movimiento') {
        mensaje.innerHTML = '¿Estás seguro de autorizar este movimiento? El movimiento se procesará inmediatamente.';
        form.action = `/caja/movimiento/${id}/autorizar`;
    } else {
        mensaje.innerHTML = '¿Estás seguro de autorizar esta transferencia? Los fondos serán transferidos entre las cajas.';
        form.action = `/caja/transferencia/${id}/autorizar`;
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModal() {
    const modal = document.getElementById('modalAutorizacion');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection