@extends('layouts.app')

@section('title', 'Operaciones de Caja')
@section('page-title', 'Operaciones de Caja')
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
        <span class="font-medium text-gray-700">Operaciones</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

{{-- 🔥 INFORMACIÓN DE LA CAJA ACTUAL (SIEMPRE VISIBLE) --}}
<div class="p-4 mb-6 border border-indigo-200 shadow-sm bg-gradient-to-r from-indigo-50 to-cyan-50 rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full">
                <span class="text-2xl">🏦</span>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">{{ $apertura->caja->nombre }}</h3>
                <p class="text-sm text-gray-500">Código: {{ $apertura->caja->codigo }} | Abierta por: {{ $apertura->usuario->name }}</p>
                <p class="text-xs text-gray-400">Fecha de apertura: {{ $apertura->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-green-600">${{ number_format($resumen['saldo_esperado'], 2) }}</p>
            <p class="text-xs text-gray-500">Saldo actual</p>
        </div>
    </div>
</div>

{{-- 🔥 SELECTOR DE CAJA PARA SUPER ADMIN Y ADMINISTRADOR --}}
@if((auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Administrador')) && isset($todasAperturas) && $todasAperturas->count() > 1)
<div class="p-4 mb-6 border border-blue-200 bg-blue-50 rounded-xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-lg">🔄</span>
            <span class="font-medium text-gray-700">Cambiar a otra caja:</span>
        </div>
        <div>
            <form action="{{ route('cajas.cambiar') }}" method="POST" class="inline">
                @csrf
                <select name="apertura_id" onchange="this.form.submit()" class="px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar caja...</option>
                    @foreach($todasAperturas as $cajaOption)
                        <option value="{{ $cajaOption->id }}" {{ $apertura->id == $cajaOption->id ? 'selected' : '' }}>
                            {{ $cajaOption->caja->nombre }} ({{ $cajaOption->caja->codigo }}) - {{ $cajaOption->usuario->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <div class="sticky p-6 bg-white shadow-lg rounded-3xl top-24">
            <div class="mb-6 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">💰</div>
                <h3 class="text-lg font-bold text-slate-800">Resumen del día</h3>
                <p class="text-sm text-gray-500">{{ $resumen['fecha'] }}</p>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between p-3 bg-gray-50 rounded-xl">
                    <span class="text-sm text-gray-600">Apertura</span>
                    <span class="font-semibold text-green-600">${{ number_format(floatval($resumen['apertura']), 2) }}</span>
                </div>
                <div class="flex justify-between p-3 bg-blue-50 rounded-xl">
                    <span class="text-sm text-gray-600">Ingresos</span>
                    <span class="font-semibold text-blue-600">+ ${{ number_format(floatval($resumen['total_ingresos']), 2) }}</span>
                </div>
                <div class="flex justify-between p-3 bg-red-50 rounded-xl">
                    <span class="text-sm text-gray-600">Egresos</span>
                    <span class="font-semibold text-red-600">- ${{ number_format(floatval($resumen['total_egresos']), 2) }}</span>
                </div>
                <div class="flex justify-between p-3 bg-orange-50 rounded-xl">
                    <span class="text-sm text-gray-600">Retiros parciales</span>
                    <span class="font-semibold text-orange-600">- ${{ number_format($resumen['total_retiros'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between p-3 border-t-2 border-green-200 bg-green-50 rounded-xl">
                    <span class="text-sm font-semibold text-gray-700">Saldo esperado</span>
                    <span class="font-bold text-green-700">${{ number_format(floatval($resumen['saldo_esperado']), 2) }}</span>
                </div>
            </div>

            <div class="pt-4 mt-6 border-t">
                <h4 class="mb-3 font-semibold text-slate-700">Por forma de pago</h4>
                <div class="space-y-2 text-sm">
                    @forelse($resumen['por_forma_pago'] as $forma => $monto)
                        @php
                            $icono = '💰';
                            $nombre = ucfirst(str_replace('_', ' ', $forma));
                            if (isset($formasPago) && $formasPago->count() > 0) {
                                $formaPagoObj = $formasPago->firstWhere('clave', $forma);
                                if ($formaPagoObj && $formaPagoObj->icono) {
                                    $icono = $formaPagoObj->icono;
                                    $nombre = $formaPagoObj->nombre;
                                }
                            }
                        @endphp
                        <div class="flex justify-between">
                            <span>{!! $icono !!} {{ $nombre }}</span>
                            <span>${{ number_format(floatval($monto), 2) }}</span>
                        </div>
                    @empty
                        <div class="py-2 text-center text-gray-400">No hay movimientos registrados</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('cajas.reporte.dia', $apertura->id) }}" class="block px-4 py-2 text-center text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
                    📊 Ver reporte completo
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-6 lg:col-span-2">
        {{-- Registrar movimiento --}}
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
                            @foreach($formasPago as $forma)
                                <option value="{{ $forma->clave }}">{!! $forma->icono !!} {{ $forma->nombre }}</option>
                            @endforeach
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
                            <option value="retiro_parcial">💸 Retiro parcial</option>
                            <option value="transferencia">🔄 Transferencia enviada</option>
                            <option value="ajuste">⚙️ Ajuste negativo</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Monto *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                        <input type="number" name="monto" step="0.01" min="0.01" required class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Concepto *</label>
                    <textarea name="concepto" rows="2" required class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500" placeholder="Ej: Venta de producto X, Pago a proveedor, etc."></textarea>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Referencia (opcional)</label>
                    <input type="text" name="referencia" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500" placeholder="Factura, ticket, etc.">
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

        {{-- Retiro Parcial de Caja --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 text-xl bg-red-100 rounded-full">💸</div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Retiro Parcial de Caja</h3>
                    <p class="text-sm text-gray-500">Registra una salida de dinero de la caja</p>
                </div>
            </div>

            <form action="{{ route('cajas.retiro.registrar') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="apertura_id" value="{{ $apertura->id }}">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium">Forma de pago *</label>
                        <select name="forma_pago" required class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            @foreach($formasPago as $forma)
                                <option value="{{ $forma->clave }}">{!! $forma->icono !!} {{ $forma->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium">Monto *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                            <input type="number" name="monto" step="0.01" min="0.01" required class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Saldo disponible: ${{ number_format($resumen['saldo_esperado'], 2) }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Motivo del retiro *</label>
                    <textarea name="motivo" rows="2" required class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Pago a proveedor, Compra de insumos, Retiro de excedente, etc."></textarea>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Referencia (opcional)</label>
                    <input type="text" name="referencia" class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="requiere_autorizacion" value="1" id="requiere_autorizacion_retiro">
                    <label for="requiere_autorizacion_retiro" class="text-sm text-gray-700">Requiere autorización de administrador</label>
                </div>

                <button type="submit" class="w-full py-3 font-semibold text-white transition bg-red-600 rounded-xl hover:bg-red-700">
                    💸 Registrar Retiro
                </button>
            </form>
        </div>

        {{-- Movimientos del día --}}
        <div class="p-6 bg-white shadow-lg rounded-3xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">Movimientos del día</h3>
                <span class="text-sm text-gray-500">Total: {{ $movimientos->total() }}</span>
            </div>

            <div class="space-y-2 overflow-y-auto max-h-96">
                @forelse($movimientos as $mov)
                    <div class="flex items-center justify-between p-3 border rounded-xl hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $mov->tipo == 'ingreso' ? 'bg-green-100' : 'bg-red-100' }}">
                                <span class="text-lg">{{ $mov->tipo == 'ingreso' ? '💰' : '💸' }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ $mov->concepto }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $mov->categoria }} •
                                    @php
                                        $iconoMov = '💰';
                                        $nombreMov = $mov->forma_pago;
                                        if (isset($formasPago) && $formasPago->count() > 0) {
                                            $formaPagoObj = $formasPago->firstWhere('clave', $mov->forma_pago);
                                            if ($formaPagoObj && $formaPagoObj->icono) {
                                                $iconoMov = $formaPagoObj->icono;
                                                $nombreMov = $formaPagoObj->nombre;
                                            }
                                        }
                                    @endphp
                                    {!! $iconoMov !!} {{ $nombreMov }}
                                    @if($mov->referencia) • Ref: {{ $mov->referencia }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-right">
                                <p class="font-bold {{ $mov->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $mov->tipo == 'ingreso' ? '+' : '-' }} ${{ number_format($mov->monto, 2) }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $mov->created_at->format('H:i') }}</p>
                                @if($mov->requiere_autorizacion && !$mov->autorizado_por)
                                    <span class="text-xs text-yellow-600">⏳ Pendiente autorización</span>
                                @endif
                            </div>
                            <a href="{{ route('cajas.movimiento.ticket', $mov) }}" target="_blank"
                                class="p-1 text-gray-400 transition hover:text-indigo-600" title="Imprimir ticket">
                                🧾
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-gray-400">No hay movimientos registrados en esta caja</p>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $movimientos->links() }}
            </div>
        </div>
    </div>
</div>
{{-- Movimientos pendientes de autorización --}}
@if(($resumen['pendientes_ingresos'] ?? 0) > 0 || ($resumen['pendientes_egresos'] ?? 0) > 0)
<div class="p-4 mt-4 border border-yellow-200 bg-yellow-50 rounded-xl">
    <h4 class="mb-2 font-semibold text-yellow-800">⏳ Pendientes de autorización</h4>
    <div class="space-y-1 text-sm">
        @if(($resumen['pendientes_ingresos'] ?? 0) > 0)
            <div class="flex justify-between">
                <span>Ingresos pendientes:</span>
                <span class="font-semibold text-yellow-700">+ ${{ number_format($resumen['pendientes_ingresos'], 2) }}</span>
            </div>
        @endif
        @if(($resumen['pendientes_egresos'] ?? 0) > 0)
            <div class="flex justify-between">
                <span>Egresos pendientes:</span>
                <span class="font-semibold text-yellow-700">- ${{ number_format($resumen['pendientes_egresos'], 2) }}</span>
            </div>
        @endif
    </div>
    <p class="mt-2 text-xs text-yellow-600">Estos movimientos no afectan el saldo hasta ser autorizados.</p>
</div>
@endif
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