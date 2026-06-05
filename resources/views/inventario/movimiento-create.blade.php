@extends('layouts.app')

@section('title', 'Nuevo Movimiento')
@section('page-title', 'Nuevo Movimiento de Inventario')

@section('content')

<div class="max-w-2xl mx-auto">

    <x-alert type="error" :message="session('error')" />
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <h4 class="text-red-700 font-semibold mb-2">⚠️ Corrige los siguientes errores:</h4>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-lg p-8">
        <form action="{{ route('inventario.movimientos.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                    <select name="tipo" id="tipoMovimiento" required class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar...</option>
                        <option value="entrada">📥 Entrada</option>
                        <option value="salida">📤 Salida</option>
                        <option value="transferencia">🔄 Transferencia</option>
                        <option value="ajuste">⚙️ Ajuste</option>
                    </select>
                </div>

                <div id="transferenciaFields" class="hidden space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Sucursal Origen</label><select name="sucursal_origen_id" class="w-full border border-gray-300 rounded-xl px-4 py-3"><option value="">—</option>@foreach($sucursales as $suc) <option value="{{ $suc->id }}">{{ $suc->nombre }}</option> @endforeach</select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Sucursal Destino</label><select name="sucursal_destino_id" class="w-full border border-gray-300 rounded-xl px-4 py-3"><option value="">—</option>@foreach($sucursales as $suc) <option value="{{ $suc->id }}">{{ $suc->nombre }}</option> @endforeach</select></div>
                    </div>
                </div>

                <div><label class="block text-sm font-medium text-gray-700 mb-2">Motivo *</label>
                    <select name="motivo" required class="w-full border border-gray-300 rounded-xl px-4 py-3">
                        <option value="compra">Compra</option><option value="venta">Venta</option><option value="devolucion">Devolución</option><option value="merma">Merma</option><option value="transferencia">Transferencia</option><option value="ajuste_inventario">Ajuste de Inventario</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Producto</label><select name="producto_id" class="w-full border border-gray-300 rounded-xl px-4 py-3"><option value="">—</option>@foreach($productos as $prod) <option value="{{ $prod->id }}">{{ $prod->nombre }}</option> @endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Insumo</label><select name="insumo_id" class="w-full border border-gray-300 rounded-xl px-4 py-3"><option value="">—</option>@foreach($insumos as $ins) <option value="{{ $ins->id }}">{{ $ins->nombre }}</option> @endforeach</select></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Cantidad *</label><input type="number" name="cantidad" required min="0.01" step="0.01" class="w-full border border-gray-300 rounded-xl px-4 py-3"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-2">Costo Unitario $ *</label><input type="number" name="costo_unitario" required min="0" step="0.01" class="w-full border border-gray-300 rounded-xl px-4 py-3"></div>
                </div>

                <div><label class="block text-sm font-medium text-gray-700 mb-2">Observación</label><textarea name="observacion" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-3"></textarea></div>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('inventario.movimientos') }}" class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">Cancelar</a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl transition font-semibold shadow-lg">💾 Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('tipoMovimiento').addEventListener('change', function() {
    document.getElementById('transferenciaFields').classList.toggle('hidden', this.value !== 'transferencia');
});
</script>
@endsection