{{-- resources/views/cajas/operaciones.blade.php --}}
@extends('layouts.app')

@section('title', 'Operaciones de Caja')
@section('page-title', 'Operaciones de Caja')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cajas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Cajas
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Operaciones</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Resumen de caja --}}
    <div class="lg:col-span-1">
        <div class="sticky p-6 bg-white shadow-lg rounded-3xl top-24">
            <div class="mb-6 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">💰</div>
                <h3 class="text-lg font-bold text-slate-800">Resumen del día</h3>
                <p class="text-sm text-gray-500">{{ $resumen['fecha'] }}</p>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <span class="text-sm text-gray-600">Apertura</span>
                    <span class="font-semibold text-green-600">${{ number_format($resumen['apertura'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                    <span class="text-sm text-gray-600">Ingresos</span>
                    <span class="font-semibold text-blue-600">+ ${{ number_format($resumen['total_ingresos'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-xl">
                    <span class="text-sm text-gray-600">Egresos</span>
                    <span class="font-semibold text-red-600">- ${{ number_format($resumen['total_egresos'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 border-t-2 border-green-200 bg-green-50 rounded-xl">
                    <span class="text-sm font-semibold text-gray-700">Saldo esperado</span>
                    <span class="font-bold text-green-700">${{ number_format($resumen['saldo_esperado'], 2) }}</span>
                </div>
            </div>

            <div class="pt-4 mt-6 border-t">
                <h4 class="mb-3 font-semibold text-slate-700">Por forma de pago</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>💵 Efectivo</span>
                        <span>${{ number_format($resumen['por_forma_pago']['efectivo'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>💳 Tarjeta Débito</span>
                        <span>${{ number_format($resumen['por_forma_pago']['tarjeta_debito'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>💎 Tarjeta Crédito</span>
                        <span>${{ number_format($resumen['por_forma_pago']['tarjeta_credito'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>🎫 Vale</span>
                        <span>${{ number_format($resumen['por_forma_pago']['vale'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>🏦 Transferencia</span>
                        <span>${{ number_format($resumen['por_forma_pago']['transferencia'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>📄 Cheque</span>
                        <span>${{ number_format($resumen['por_forma_pago']['cheque'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('cajas.reporte.dia', $apertura->id) }}" class="block px-4 py-2 text-center text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
                    📊 Ver reporte completo
                </a>
            </div>
        </div>
    </div>

    {{-- Movimientos y registro --}}
    <div class="space-y-6 lg:col-span-2">
        {{-- Formulario de registro --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">Registrar movimiento</h3>
            
            <form action="{{ route('cajas.movimiento.registrar') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="apertura_id" value="{{ $apertura->id }}">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium">Tipo *</label>
                        <select name="tipo" required class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500" onchange="toggleCategorias(this)">
                            <option value="ingreso">💰 Ingreso</option>
                            <option value="egreso">💸 Egreso</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Forma de pago *</label>
                        <select name="forma_pago" required class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <option value="efectivo">💵 Efectivo</option>
                            <option value="tarjeta_debito">💳 Tarjeta Débito</option>
                            <option value="tarjeta_credito">💎 Tarjeta Crédito</option>
                            <option value="vale">🎫 Vale</option>
                            <option value="transferencia">🏦 Transferencia</option>
                            <option value="cheque">📄 Cheque</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Categoría *</label>
                    <select name="categoria" id="categoria" required class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <optgroup label="Ingresos" id="ingresos-group">
                            <option value="venta">🛒 Venta</option>
                            <option value="abono_credito">📋 Abono a crédito</option>
                            <option value="cobro_servicio">🔧 Cobro de servicio</option>
                            <option value="prestamo">🏦 Préstamo recibido</option>
                            <option value="transferencia">🔄 Transferencia recibida</option>
                            <option value="ajuste">⚙️ Ajuste positivo</option>
                        </optgroup>
                        <optgroup label="Egresos" id="egresos-group" style="display:none">
                            <option value="compra">📦 Compra</option>
                            <option value="gasto">📝 Gasto operativo</option>
                            <option value="retiro">💸 Retiro personal</option>
                            <option value="transferencia">🔄 Transferencia enviada</option>
                            <option value="ajuste">⚙️ Ajuste negativo</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Monto *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                        <input type="number" name="monto" step="0.01" min="0.01" required
                            class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Concepto *</label>
                    <textarea name="concepto" rows="2" required class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Referencia (opcional)</label>
                    <input type="text" name="referencia" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="requiere_autorizacion" value="1" id="requiere_autorizacion">
                    <label for="requiere_autorizacion" class="text-sm text-gray-700">Requiere autorización de administrador</label>
                </div>

                <button type="submit" class="w-full py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                    Registrar movimiento
                </button>
            </form>
        </div>

        {{-- Lista de movimientos --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <h3 class="mb-4 text-lg font-bold text-slate-800">Movimientos del día</h3>
            
            <div class="space-y-2 overflow-y-auto max-h-96">
                @forelse($movimientos as $mov)
                <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $mov->tipo == 'ingreso' ? 'bg-green-100' : 'bg-red-100' }}">
                            <span class="text-lg">{{ $mov->tipo == 'ingreso' ? '💰' : '💸' }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $mov->concepto }}</p>
                            <p class="text-xs text-gray-500">{{ $mov->categoria }} • {{ $mov->forma_pago }}</p>
                            @if($mov->referencia)
                            <p class="text-xs text-gray-400">Ref: {{ $mov->referencia }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold {{ $mov->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $mov->tipo == 'ingreso' ? '+' : '-' }} ${{ number_format($mov->monto, 2) }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $mov->created_at->format('H:i') }}</p>
                        @if($mov->requiere_autorizacion && !$mov->autorizado_por)
                        <span class="text-xs text-yellow-600">⏳ Pendiente autorización</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="py-6 text-center text-gray-400">No hay movimientos registrados</p>
                @endforelse
            </div>
            
            <div class="mt-4">
                {{ $movimientos->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function toggleCategorias(select) {
    const tipo = select.value;
    const ingresosGroup = document.getElementById('ingresos-group');
    const egresosGroup = document.getElementById('egresos-group');
    const categoriaSelect = document.getElementById('categoria');
    
    if (tipo === 'ingreso') {
        ingresosGroup.style.display = '';
        egresosGroup.style.display = 'none';
        categoriaSelect.value = 'venta';
    } else {
        ingresosGroup.style.display = 'none';
        egresosGroup.style.display = '';
        categoriaSelect.value = 'compra';
    }
}
</script>
@endsection