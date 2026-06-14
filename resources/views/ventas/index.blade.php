{{-- resources/views/ventas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Punto de Venta')
@section('page-title', 'Punto de Venta')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Punto de Venta</span>
    </li>
@endsection

@section('content')

    {{-- ✅ SELECTOR DE CAJA PARA MÚLTIPLES CAJAS --}}
    @if(isset($cajasActivas) && $cajasActivas->count() > 1)
        <div class="p-4 mb-4 bg-white border border-indigo-200 shadow-sm rounded-2xl">
            <div class="flex items-center gap-3">
                <span class="text-lg">🏦</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700">Selecciona la caja para operar:</p>
                    <select id="cajaActivaSelect"
                        class="w-full px-4 py-2 mt-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                        onchange="guardarCajaSeleccionada()">
                        @foreach($cajasActivas as $caja)
                            <option value="{{ $caja->id }}" {{ $loop->first ? 'selected' : '' }}>
                                🏦 {{ $caja->caja->nombre }} | 👤 {{ $caja->usuario->name }} | 💰
                                ${{ number_format($caja->monto_inicial + $caja->total_ingresos - $caja->total_egresos, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @elseif(isset($cajaAbierta) && $cajaAbierta)
        <div class="p-3 mb-4 border border-green-200 bg-green-50 rounded-xl">
            <div class="flex items-center gap-2 text-sm text-green-700">
                <span>🏦</span>
                <span class="font-medium">{{ $cajaAbierta->caja->nombre }}</span>
                <span class="text-green-400">|</span>
                <span>👤 {{ $cajaAbierta->usuario->name }}</span>
                <span class="text-green-400">|</span>
                <span>💰
                    ${{ number_format($cajaAbierta->monto_inicial + $cajaAbierta->total_ingresos - $cajaAbierta->total_egresos, 2) }}</span>
            </div>
        </div>
    @else
        <div class="flex items-center justify-between p-4 mb-4 border-l-4 border-yellow-400 bg-yellow-50 rounded-r-xl">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <p class="font-medium text-yellow-800">No hay una caja abierta</p>
                    <p class="text-sm text-yellow-700">Para realizar ventas, debes abrir una caja primero.</p>
                </div>
            </div>
            @can('abrir_caja')
                <a href="{{ route('cajas.apertura') }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-lg hover:bg-yellow-600">🔓 Abrir caja</a>
            @endcan
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Panel de productos --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden bg-white shadow-lg rounded-2xl">
                <div class="p-4 border-b bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex flex-wrap gap-2">
                        <div class="relative flex-1">
                            <svg class="absolute w-5 h-5 text-gray-400 left-3 top-2.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text" id="buscarProducto"
                                placeholder="Buscar por nombre, SKU o código de barras..."
                                class="w-full py-2 pl-10 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <select id="categoriaFilter"
                            class="px-4 py-2 bg-white border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 p-4 md:grid-cols-3 lg:grid-cols-4 max-h-[600px] overflow-y-auto">
                    @foreach($productos as $producto)
                        <div class="p-3 transition-all duration-200 border cursor-pointer producto-card group rounded-xl hover:shadow-lg hover:border-indigo-300 hover:-translate-y-1"
                            data-id="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}"
                            data-precio="{{ $producto->precio_venta }}" data-stock="{{ $producto->stock }}">
                            <div class="relative mb-2 overflow-hidden bg-gray-100 rounded-lg aspect-square">
                                @if($producto->imagen_principal)
                                    <img src="{{ $producto->imagen_principal }}"
                                        class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="flex items-center justify-center w-full h-full">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                @endif
                                @if($producto->stock <= 5) <span
                                    class="absolute px-2 py-0.5 text-xs text-white bg-red-500 rounded-full top-2 right-2">Stock
                                    bajo</span>
                                @endif
                            </div>
                            <h4 class="text-sm font-medium truncate text-slate-800">{{ $producto->nombre }}</h4>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-lg font-bold text-indigo-600">${{ number_format($producto->precio_venta, 2) }}
                                </p>
                                <p class="text-xs text-gray-400">Stock: {{ number_format($producto->stock, 0) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Panel del carrito --}}
        <div class="lg:col-span-1">
            <div class="sticky overflow-hidden bg-white shadow-lg rounded-2xl top-24">
                <div class="p-4 border-b bg-gradient-to-r from-indigo-600 to-cyan-500">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-white/20 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 18v3">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white">Carrito de Compras</h3>
                        </div>
                        <button id="limpiarCarrito"
                            class="px-3 py-1 text-sm font-medium text-red-600 transition-all bg-white rounded-lg hover:bg-red-50 hover:shadow">
                            🗑️ Limpiar todo
                        </button>
                    </div>
                </div>

                <div id="carritoLista" class="p-4 space-y-3 max-h-[400px] overflow-y-auto bg-gray-50">
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 18v3"></path>
                        </svg>
                        <p class="mt-2 text-gray-400">🛒 No hay productos en el carrito</p>
                        <p class="text-xs text-gray-400">Selecciona productos para comenzar</p>
                    </div>
                </div>

                <div class="p-4 bg-white border-t">
                    <div class="space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span id="subtotal" class="font-semibold">$0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>IVA (16%):</span>
                            <span id="iva" class="font-semibold">$0.00</span>
                        </div>
                        <div class="flex justify-between pt-2 text-lg font-bold border-t border-gray-200">
                            <span>Total:</span>
                            <span id="total" class="text-indigo-600">$0.00</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-4">
                        <button id="btnVentaContado"
                            class="flex items-center justify-center gap-1 px-2 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-green-600 rounded-xl hover:bg-green-700 hover:shadow-lg active:scale-95">
                            💵 Contado
                        </button>
                        <button id="btnVentaCredito"
                            class="flex items-center justify-center gap-1 px-2 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-blue-600 rounded-xl hover:bg-blue-700 hover:shadow-lg active:scale-95">
                            📋 Crédito
                        </button>
                        <button id="btnCotizacion"
                            class="flex items-center justify-center gap-1 px-2 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-orange-600 rounded-xl hover:bg-orange-700 hover:shadow-lg active:scale-95">
                            📄 Cotizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de crédito --}}
    <div id="modalCredito" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md p-6 bg-white shadow-2xl rounded-2xl animate-fade-in-up">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-800">Venta a Crédito</h3>
                <button onclick="cerrarModalCredito()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Cliente *</label>
                    <select id="clienteId" required
                        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Plazo *</label>
                    <select id="plazo" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="7_dias">📅 7 días</option>
                        <option value="15_dias">📅 15 días</option>
                        <option value="1_mes">📅 1 mes</option>
                        <option value="2_meses">📅 2 meses</option>
                        <option value="3_meses">📅 3 meses</option>
                        <option value="6_meses">📅 6 meses</option>
                        <option value="1_ano">📅 1 año</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Número de pagos *</label>
                    <input type="number" id="numPagos" value="1" min="1" max="12"
                        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Monto por pago: <span id="montoPorPago"
                            class="font-semibold text-indigo-600">$0.00</span></p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total a pagar:</span>
                        <span id="totalCredito" class="text-xl font-bold text-indigo-600">$0.00</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button onclick="cerrarModalCredito()"
                    class="px-4 py-2 border rounded-xl hover:bg-gray-50">Cancelar</button>
                <button id="confirmarCredito"
                    class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Generar Crédito</button>
            </div>
        </div>
    </div>

    {{-- Modal de pago mejorado --}}
    <div id="modalPago" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-4xl p-6 bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto animate-fade-in-up">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-800">Formas de Pago</h3>
                <button onclick="cerrarModalPago()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- Resumen de la venta --}}
            <div
                class="grid grid-cols-1 gap-4 p-4 mb-4 bg-gradient-to-r from-indigo-50 to-cyan-50 rounded-xl md:grid-cols-4">
                <div class="text-center">
                    <span class="text-sm text-gray-500">Subtotal</span>
                    <p id="subtotalModal" class="text-xl font-bold text-indigo-600">$0.00</p>
                </div>
                <div class="text-center">
                    <span class="text-sm text-gray-500">IVA (16%)</span>
                    <p id="ivaModal" class="text-xl font-bold text-indigo-600">$0.00</p>
                </div>
                <div class="text-center">
                    <span class="text-sm text-gray-500">Total a pagar</span>
                    <p id="totalPagar" class="text-2xl font-bold text-indigo-600">$0.00</p>
                </div>
                <div class="text-center">
                    <span class="text-sm text-gray-500">Pagado</span>
                    <p id="totalPagado" class="text-2xl font-bold text-green-600">$0.00</p>
                </div>
            </div>

            {{-- 🔥 FALTANTE / POR PAGAR (EN ROJO) --}}
            <div class="p-3 mb-4 text-center bg-red-50 rounded-xl">
                <span class="text-sm text-gray-600">Faltante / Por pagar:</span>
                <span id="faltanteMonto" class="ml-2 text-2xl font-bold text-red-600">$0.00</span>
            </div>

            {{-- Barra de progreso --}}
            <div class="mb-4">
                <div class="flex justify-between mb-1 text-sm">
                    <span>Progreso de pago:</span>
                    <span id="porcentajeProgreso" class="font-medium">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBar" class="bg-green-600 h-2.5 rounded-full transition-all" style="width: 0%"></div>
                </div>
            </div>

            {{-- Tipo de pago: Simple o Mixto --}}
            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">Tipo de pago</label>
                <div class="flex gap-3">
                    <button type="button" id="btnPagoSimple"
                        class="flex-1 py-2 text-sm font-medium text-white bg-indigo-600 rounded-xl">💵 Pago simple</button>
                    <button type="button" id="btnPagoMixto"
                        class="flex-1 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl">🔄 Pago mixto</button>
                </div>
            </div>

            {{-- Modo Simple (una sola forma de pago, por defecto efectivo) --}}
            <div id="modoSimple">
                <div class="p-4 border rounded-xl bg-gray-50">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-2xl">💵</span>
                        <span class="font-medium">Efectivo</span>
                    </div>
                    <div class="relative">
                        <span class="absolute text-gray-500 left-3 top-2.5">$</span>
                        <input type="number" id="montoSimple" step="0.01" min="0"
                            class="w-full py-3 pl-8 pr-4 text-lg border rounded-xl focus:ring-2 focus:ring-indigo-500"
                            placeholder="Monto recibido">
                    </div>
                    <div id="cambioInfo" class="hidden p-3 mt-3 bg-green-100 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-green-800">💰 Cambio a devolver:</span>
                            <span id="cambioMonto" class="text-xl font-bold text-green-700">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modo Mixto (múltiples formas de pago) - DISEÑO ORIGINAL --}}
            <div id="modoMixto" class="hidden">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($formasPago as $forma)
                        <div class="p-3 transition-all duration-200 bg-white border pago-card rounded-xl hover:shadow-md hover:border-indigo-300"
                            data-forma-id="{{ $forma->id }}" data-forma-clave="{{ $forma->clave }}"
                            data-requiere-referencia="{{ $forma->requiere_referencia ? '1' : '0' }}">
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg">
                                    <span class="text-xl">{!! $forma->icono !!}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 truncate">{{ $forma->nombre }}</p>
                                    <div class="mt-2 space-y-2">
                                        <div class="relative">
                                            <span class="absolute text-gray-500 -translate-y-1/2 left-3 top-1/2">$</span>
                                            <input type="number"
                                                class="monto-pago-input w-full pl-7 pr-2 py-1.5 text-sm border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                                placeholder="0.00" min="0" step="0.01" value="0">
                                        </div>
                                        <input type="text"
                                            class="referencia-input hidden w-full px-2 py-1.5 text-sm border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Referencia">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 🔥 Cambio en efectivo para modo mixto --}}
                <div id="cambioInfoMixto" class="hidden p-3 mt-4 bg-green-100 rounded-xl">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-green-800">💰 Cambio total a devolver:</span>
                        <span id="cambioMontoMixto" class="text-xl font-bold text-green-700">$0.00</span>
                    </div>
                    <p class="mt-1 text-xs text-green-600">El cambio se entregará en efectivo</p>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex justify-end gap-3 pt-4 mt-4 border-t">
                <button onclick="cerrarModalPago()" class="px-4 py-2 border rounded-xl hover:bg-gray-50">Cancelar</button>
                <button id="confirmarPago"
                    class="px-6 py-2 text-white transition-all duration-200 bg-green-600 rounded-xl hover:bg-green-700 hover:shadow-lg">
                    ✅ Confirmar Pago
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let carrito = [];
            let formasPagoData = @json($formasPago);
            let modoPago = 'simple';
            let totalVenta = 0;

            // ==================== FUNCIONES DEL CARRITO ====================

            async function cargarCarrito() {
                try {
                    const response = await axios.get('{{ route("carrito.obtener") }}');
                    if (response.data.success) {
                        let items = response.data.items;
                        if (!items || typeof items !== 'object') items = [];
                        if (!Array.isArray(items)) items = Object.values(items);
                        carrito = items.map(item => ({
                            ...item,
                            precio: parseFloat(item.precio) || 0,
                            cantidad: parseInt(item.cantidad) || 0
                        }));
                        actualizarCarrito();
                    }
                } catch (error) {
                    console.error('Error al cargar carrito:', error);
                    Swal.fire('Error', 'No se pudo cargar el carrito', 'error');
                }
            }

            async function agregarProducto(productoId, cantidad = 1) {
                try {
                    const response = await axios.post('{{ route("carrito.agregar") }}', {
                        producto_id: productoId,
                        cantidad: cantidad
                    });
                    if (response.data.success) {
                        carrito = (response.data.items || []).map(item => ({
                            ...item,
                            precio: parseFloat(item.precio) || 0,
                            cantidad: parseInt(item.cantidad) || 0
                        }));
                        actualizarCarrito();
                        window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                        return { success: true };
                    }
                } catch (error) {
                    Swal.fire('Error', error.response?.data?.message || 'Error al agregar producto', 'error');
                    return { success: false };
                }
            }

            async function eliminarProducto(index) {
                const confirm = await Swal.fire({
                    title: '¿Eliminar producto?',
                    text: 'El producto será removido del carrito',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });
                if (confirm.isConfirmed) {
                    try {
                        const response = await axios.delete(`{{ url("carrito/item") }}/${index}`);
                        if (response.data.success) {
                            carrito = (response.data.items || []).map(item => ({
                                ...item,
                                precio: parseFloat(item.precio) || 0,
                                cantidad: parseInt(item.cantidad) || 0
                            }));
                            actualizarCarrito();
                            window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                            Swal.fire('Eliminado', response.data.message, 'success');
                        }
                    } catch (error) {
                        Swal.fire('Error', error.response?.data?.message || 'Error al eliminar producto', 'error');
                    }
                }
            }

            async function limpiarCarrito() {
                if (carrito.length === 0) return;
                const confirm = await Swal.fire({
                    title: '¿Limpiar carrito?',
                    text: 'Se eliminarán todos los productos',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Sí, limpiar',
                    cancelButtonText: 'Cancelar'
                });
                if (confirm.isConfirmed) {
                    try {
                        const response = await axios.delete('{{ route("carrito.limpiar") }}');
                        if (response.data.success) {
                            carrito = [];
                            actualizarCarrito();
                            window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                            Swal.fire('Limpiado', response.data.message, 'success');
                        }
                    } catch (error) {
                        Swal.fire('Error', error.response?.data?.message || 'Error al limpiar carrito', 'error');
                    }
                }
            }

            async function actualizarCantidad(index, nuevaCantidad) {
                if (nuevaCantidad < 1) {
                    eliminarProducto(index);
                    return;
                }
                try {
                    const response = await axios.put(`{{ url("carrito/item") }}/${index}/cantidad`, {
                        cantidad: nuevaCantidad
                    });
                    if (response.data.success) {
                        carrito = (response.data.items || []).map(item => ({
                            ...item,
                            precio: parseFloat(item.precio) || 0,
                            cantidad: parseInt(item.cantidad) || 0
                        }));
                        actualizarCarrito();
                        window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                    }
                } catch (error) {
                    Swal.fire('Error', error.response?.data?.message || 'Error al actualizar cantidad', 'error');
                }
            }

            function actualizarCarrito() {
                const container = document.getElementById('carritoLista');
                let subtotal = 0;

                if (!carrito || carrito.length === 0) {
                    container.innerHTML = `<div class="flex flex-col items-center justify-center py-12 text-center">
                                                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 21h6M12 18v3"></path>
                                                                </svg>
                                                                <p class="mt-2 text-gray-400">🛒 No hay productos en el carrito</p>
                                                                <p class="text-xs text-gray-400">Selecciona productos para comenzar</p>
                                                            </div>`;
                    document.getElementById('subtotal').innerHTML = '$0.00';
                    document.getElementById('iva').innerHTML = '$0.00';
                    document.getElementById('total').innerHTML = '$0.00';
                    return;
                }

                let html = '';
                carrito.forEach((item, index) => {
                    const precio = parseFloat(item.precio) || 0;
                    const cantidad = parseInt(item.cantidad) || 0;
                    const totalItem = precio * cantidad;
                    subtotal += totalItem;
                    html += `<div class="flex items-center justify-between p-3 transition-all bg-white rounded-lg shadow-sm hover:shadow-md">
                                                                <div class="flex-1">
                                                                    <p class="font-medium text-gray-800">${escapeHtml(item.nombre)}</p>
                                                                    <div class="flex items-center gap-2 mt-1">
                                                                        <button onclick="actualizarCantidad(${index}, ${cantidad - 1})" class="w-6 h-6 text-gray-600 transition-colors bg-gray-100 rounded-full hover:bg-red-100 hover:text-red-600">-</button>
                                                                        <span class="w-8 text-sm font-medium text-center">${cantidad}</span>
                                                                        <button onclick="actualizarCantidad(${index}, ${cantidad + 1})" class="w-6 h-6 text-gray-600 transition-colors bg-gray-100 rounded-full hover:bg-green-100 hover:text-green-600">+</button>
                                                                        <span class="text-xs text-gray-400">$${precio.toFixed(2)} c/u</span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <p class="font-semibold text-indigo-600">$${totalItem.toFixed(2)}</p>
                                                                    <button onclick="eliminarProducto(${index})" class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                                                                </div>
                                                            </div>`;
                });

                const subtotalRedondeado = Math.round(subtotal * 100) / 100;
                const iva = Math.round(subtotalRedondeado * 0.16 * 100) / 100;
                totalVenta = Math.round((subtotalRedondeado + iva) * 100) / 100;

                container.innerHTML = html;
                document.getElementById('subtotal').innerHTML = `$${subtotalRedondeado.toFixed(2)}`;
                document.getElementById('iva').innerHTML = `$${iva.toFixed(2)}`;
                document.getElementById('total').innerHTML = `$${totalVenta.toFixed(2)}`;
            }

            // ==================== ACTUALIZAR PAGOS ====================

            function actualizarTotalesGenerales(totalPagado) {
                const pagado = Math.min(totalPagado, totalVenta);
                const faltante = Math.max(0, totalVenta - totalPagado);
                const excedente = Math.max(0, totalPagado - totalVenta);

                document.getElementById('totalPagado').innerHTML = `$${pagado.toFixed(2)}`;
                document.getElementById('faltanteMonto').innerHTML = `$${faltante.toFixed(2)}`;

                const progressPercent = totalVenta > 0 ? (pagado / totalVenta) * 100 : 0;
                document.getElementById('progressBar').style.width = `${progressPercent}%`;
                document.getElementById('porcentajeProgreso').innerHTML = `${Math.round(progressPercent)}%`;

                return { pagado, faltante, excedente };
            }

            // ==================== PAGO SIMPLE ====================

            function actualizarPagoSimple() {
                const montoSimple = parseFloat(document.getElementById('montoSimple').value) || 0;
                const totalPagado = montoSimple;
                const excedente = Math.max(0, totalPagado - totalVenta);

                actualizarTotalesGenerales(totalPagado);

                const cambioInfo = document.getElementById('cambioInfo');
                const cambioMonto = document.getElementById('cambioMonto');

                if (excedente > 0) {
                    cambioInfo.classList.remove('hidden');
                    cambioMonto.innerHTML = `$${excedente.toFixed(2)}`;
                } else {
                    cambioInfo.classList.add('hidden');
                }

                return { monto: montoSimple, excedente };
            }

            function actualizarPagoMixto() {
                let totalPagado = 0;
                document.querySelectorAll('#modoMixto .monto-pago-input').forEach(input => {
                    let monto = parseFloat(input.value) || 0;
                    totalPagado += Math.round(monto * 100) / 100;
                });

                const { pagado, faltante, excedente } = actualizarTotalesGenerales(totalPagado);

                // Mostrar cambio en efectivo si hay excedente
                const cambioInfoMixto = document.getElementById('cambioInfoMixto');
                const cambioMontoMixto = document.getElementById('cambioMontoMixto');

                if (excedente > 0.01) {
                    cambioInfoMixto.classList.remove('hidden');
                    cambioMontoMixto.innerHTML = `$${excedente.toFixed(2)}`;
                } else {
                    cambioInfoMixto.classList.add('hidden');
                }

                // 🔥 Mostrar advertencia si el pago es insuficiente
                const faltanteElement = document.getElementById('faltanteMonto');
                if (faltante > 0.01) {
                    faltanteElement.classList.add('text-red-600', 'font-bold');
                }

                return { totalPagado, pagado, faltante, excedente };
            }

            // ==================== INICIALIZACIÓN MODAL ====================

            function mostrarModalPago() {
                if (carrito.length === 0) {
                    Swal.fire('Carrito vacío', 'Agrega productos al carrito', 'warning');
                    return;
                }

                // Resetear valores
                document.getElementById('montoSimple').value = '';
                document.querySelectorAll('#modoMixto .monto-pago-input').forEach(input => input.value = '0');

                // Actualizar totales en modal
                const subtotal = totalVenta / 1.16;
                const iva = totalVenta - subtotal;
                document.getElementById('subtotalModal').innerHTML = `$${subtotal.toFixed(2)}`;
                document.getElementById('ivaModal').innerHTML = `$${iva.toFixed(2)}`;
                document.getElementById('totalPagar').innerHTML = `$${totalVenta.toFixed(2)}`;
                document.getElementById('totalPagado').innerHTML = `$0.00`;
                document.getElementById('faltanteMonto').innerHTML = `$${totalVenta.toFixed(2)}`;
                document.getElementById('progressBar').style.width = '0%';
                document.getElementById('porcentajeProgreso').innerHTML = '0%';
                document.getElementById('cambioInfo')?.classList.add('hidden');
                document.getElementById('cambioInfoMixto')?.classList.add('hidden');

                // Mostrar modal
                document.getElementById('modalPago').classList.remove('hidden');
                document.getElementById('modalPago').classList.add('flex');

                setTimeout(() => document.getElementById('montoSimple')?.focus(), 100);
            }

            function cerrarModalPago() {
                document.getElementById('modalPago').classList.add('hidden');
                document.getElementById('modalPago').classList.remove('flex');
            }

            // ==================== TIPO DE PAGO ====================

            function setModoPago(modo) {
                modoPago = modo;
                const modoSimple = document.getElementById('modoSimple');
                const modoMixto = document.getElementById('modoMixto');
                const btnSimple = document.getElementById('btnPagoSimple');
                const btnMixto = document.getElementById('btnPagoMixto');

                if (modo === 'simple') {
                    modoSimple.classList.remove('hidden');
                    modoMixto.classList.add('hidden');
                    btnSimple.className = 'flex-1 py-2 text-sm font-medium text-white bg-indigo-600 rounded-xl';
                    btnMixto.className = 'flex-1 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl';
                    actualizarPagoSimple();
                } else {
                    modoSimple.classList.add('hidden');
                    modoMixto.classList.remove('hidden');
                    btnSimple.className = 'flex-1 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl';
                    btnMixto.className = 'flex-1 py-2 text-sm font-medium text-white bg-indigo-600 rounded-xl';
                    actualizarPagoMixto();
                }
            }

            async function confirmarVentaContado() {
                const cajaSelect = document.getElementById('cajaActivaSelect');
                let cajaAperturaId = cajaSelect?.value || {{ isset($cajaAbierta) && $cajaAbierta ? $cajaAbierta->id : 'null' }};

                if (!cajaAperturaId || cajaAperturaId === 'null') {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No hay caja seleccionada.' });
                    return;
                }

                let pagos = [];
                let subtotal = totalVenta / 1.16;
                let iva = totalVenta - subtotal;

                if (modoPago === 'simple') {
                    const montoSimple = parseFloat(document.getElementById('montoSimple').value) || 0;
                    if (montoSimple < totalVenta - 0.01) {
                        Swal.fire({ icon: 'warning', title: 'Pago insuficiente', text: `Faltante: $${(totalVenta - montoSimple).toFixed(2)}` });
                        return;
                    }
                    const efectivo = formasPagoData.find(f => f.clave === 'efectivo');
                    pagos.push({ forma_pago_id: efectivo?.id || formasPagoData[0]?.id, monto: totalVenta, referencia: null });
                } else {
                    // Modo mixto: recolectar pagos originales
                    const pagosOriginales = [];
                    document.querySelectorAll('#modoMixto [data-forma-id]').forEach(card => {
                        const formaPagoId = card.dataset.formaId;
                        let monto = parseFloat(card.querySelector('.monto-pago-input').value) || 0;
                        if (monto > 0) {
                            pagosOriginales.push({ forma_pago_id: formaPagoId, monto: monto, referencia: null });
                        }
                    });

                    const totalPagado = pagosOriginales.reduce((sum, p) => sum + p.monto, 0);
                    const excedente = totalPagado - totalVenta;

                    if (totalPagado < totalVenta - 0.01) {
                        Swal.fire({ icon: 'warning', title: 'Pago insuficiente', text: `Faltante: $${(totalVenta - totalPagado).toFixed(2)}` });
                        return;
                    }

                    // Ajustar pagos si hay excedente
                    if (excedente > 0) {
                        let restoExcedente = excedente;
                        for (let pago of pagosOriginales) {
                            if (restoExcedente <= 0.01) break;

                            if (pago.monto >= restoExcedente + 0.01) {
                                pago.monto = Math.round((pago.monto - restoExcedente) * 100) / 100;
                                restoExcedente = 0;
                            } else {
                                restoExcedente = Math.round((restoExcedente - pago.monto) * 100) / 100;
                                pago.monto = 0;
                            }
                        }
                    }

                    // Filtrar pagos con monto > 0
                    pagos = pagosOriginales.filter(p => p.monto > 0.01);

                    // Validar que la suma sea igual al total
                    const sumaAjustada = pagos.reduce((sum, p) => sum + p.monto, 0);
                    if (Math.abs(sumaAjustada - totalVenta) > 0.02) {
                        console.error('Error en ajuste de pagos', { sumaAjustada, totalVenta });
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Error al calcular los pagos. Intenta de nuevo.' });
                        return;
                    }
                }

                // 🔥 MOSTRAR CONFIRMACIÓN ANTES DE ENVIAR
                const confirmed = await mostrarConfirmacionVenta(pagos, totalVenta, subtotal, iva);

                if (!confirmed) {
                    return; // Usuario canceló
                }

                Swal.fire({ title: 'Procesando venta...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                try {
                    const response = await axios.post('{{ route("ventas.contado.store") }}', {
                        items: carrito,
                        pagos: pagos,
                        incluir_iva: true,
                        caja_apertura_id: cajaAperturaId,
                        observaciones: ''
                    });

                    if (response.data.success) {
                        await Swal.fire({
                            title: '🎉 ¡Venta exitosa!',
                            html: `<div class="text-center">
                            <p class="text-2xl font-bold text-indigo-600">${response.data.folio}</p>
                            <p class="mt-2">Total: <strong>$${totalVenta.toFixed(2)}</strong></p>
                        </div>`,
                            icon: 'success',
                            confirmButtonText: '🧾 Imprimir ticket',
                            showCancelButton: true,
                            cancelButtonText: 'Nueva venta',
                            confirmButtonColor: '#4f46e5'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(`/ventas/${response.data.venta_id}/ticket`, '_blank');
                            }
                        });

                        carrito = [];
                        actualizarCarrito();
                        window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                        cerrarModalPago();
                    } else {
                        throw new Error(response.data.message || 'Error desconocido');
                    }
                } catch (error) {
                    const errorMsg = error.response?.data?.message || error.message || 'Error al registrar venta';
                    Swal.fire('Error', errorMsg, 'error');
                }
            }

            // ==================== VENTA CRÉDITO ====================

            function mostrarModalCredito() {
                if (carrito.length === 0) {
                    Swal.fire('Carrito vacío', 'Agrega productos al carrito', 'warning');
                    return;
                }
                document.getElementById('totalCredito').innerText = totalVenta.toFixed(2);
                document.getElementById('numPagos').value = 1;
                calcularMontoPorPago();
                document.getElementById('modalCredito').classList.remove('hidden');
                document.getElementById('modalCredito').classList.add('flex');
            }

            function cerrarModalCredito() {
                document.getElementById('modalCredito').classList.add('hidden');
                document.getElementById('modalCredito').classList.remove('flex');
            }

            function calcularMontoPorPago() {
                const numPagos = parseInt(document.getElementById('numPagos').value) || 1;
                const montoPorPago = totalVenta / numPagos;
                document.getElementById('montoPorPago').innerHTML = `$${montoPorPago.toFixed(2)}`;
            }

            async function confirmarVentaCredito() {
                const clienteId = document.getElementById('clienteId').value;
                if (!clienteId) {
                    Swal.fire('Cliente requerido', 'Selecciona un cliente para la venta a crédito', 'warning');
                    return;
                }

                const confirm = await Swal.fire({
                    title: '¿Confirmar venta a crédito?',
                    text: 'Se generarán los pagarés correspondientes',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, generar crédito',
                    cancelButtonText: 'Cancelar'
                });

                if (confirm.isConfirmed) {
                    try {
                        const response = await axios.post('{{ route("ventas.credito.store") }}', {
                            items: carrito,
                            cliente_id: clienteId,
                            plazo: document.getElementById('plazo').value,
                            num_pagos: document.getElementById('numPagos').value,
                            incluir_iva: true
                        });
                        if (response.data.success) {
                            Swal.fire({
                                title: '¡Crédito registrado!',
                                text: `Venta ${response.data.folio} - Crédito generado`,
                                icon: 'success',
                                confirmButtonText: 'Imprimir pagarés'
                            }).then(() => {
                                window.open(`/ventas/credito/${response.data.credito_id}/pagares`, '_blank');
                                carrito = [];
                                actualizarCarrito();
                                window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                                cerrarModalCredito();
                            });
                        }
                    } catch (error) {
                        Swal.fire('Error', error.response?.data?.message || 'Error al registrar crédito', 'error');
                    }
                }
            }

            // ==================== COTIZACIÓN ====================

            async function generarCotizacion() {
                if (carrito.length === 0) {
                    Swal.fire('Carrito vacío', 'Agrega productos al carrito', 'warning');
                    return;
                }

                const { value: formValues } = await Swal.fire({
                    title: 'Generar Cotización',
                    html: `<div class="text-left">
                                                                <div class="mb-3">
                                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Cliente (opcional)</label>
                                                                    <select id="clienteCotizacion" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                                                        <option value="">Cliente mostrador</option>
                                                                        @foreach($clientes as $cliente)
                                                                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Vigencia de la cotización *</label>
                                                                    <select id="diasValidez" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                                                        <option value="1">📅 1 día</option>
                                                                        <option value="3">📅 3 días</option>
                                                                        <option value="5">📅 5 días</option>
                                                                        <option value="7" selected>📅 7 días (1 semana)</option>
                                                                        <option value="15">📅 15 días</option>
                                                                        <option value="30">📅 30 días (1 mes)</option>
                                                                        <option value="60">📅 60 días (2 meses)</option>
                                                                        <option value="90">📅 90 días (3 meses)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Observaciones</label>
                                                                    <textarea id="observaciones" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                                                                </div>
                                                            </div>`,
                    showCancelButton: true,
                    confirmButtonText: '📄 Generar Cotización',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#f97316',
                    preConfirm: () => ({
                        clienteId: document.getElementById('clienteCotizacion').value,
                        diasValidez: parseInt(document.getElementById('diasValidez').value),
                        observaciones: document.getElementById('observaciones').value
                    })
                });

                if (formValues) {
                    try {
                        const response = await axios.post('{{ route("cotizaciones.store") }}', {
                            items: carrito,
                            cliente_id: formValues.clienteId,
                            dias_validez: formValues.diasValidez,
                            observaciones: formValues.observaciones
                        });
                        if (response.data.success) {
                            Swal.fire({
                                title: '¡Cotización generada!',
                                html: `<div class="text-center"><p class="text-2xl font-bold text-orange-600">${response.data.folio}</p><p class="mt-2 text-sm">Válida por <strong>${formValues.diasValidez} días</strong></p></div>`,
                                icon: 'success',
                                confirmButtonText: '📄 Ver PDF',
                                confirmButtonColor: '#f97316'
                            }).then((result) => {
                                if (result.isConfirmed) window.open(`/cotizaciones/${response.data.id}/pdf`, '_blank');
                            });
                            carrito = [];
                            actualizarCarrito();
                            window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                        }
                    } catch (error) {
                        Swal.fire('Error', error.response?.data?.message || 'Error al generar cotización', 'error');
                    }
                }
            }

            // ==================== FILTROS Y EVENTOS ====================

            function filtrarProductos() {
                const searchTerm = document.getElementById('buscarProducto').value.toLowerCase();
                document.querySelectorAll('.producto-card').forEach(card => {
                    const nombre = card.dataset.nombre.toLowerCase();
                    card.style.display = nombre.includes(searchTerm) ? 'block' : 'none';
                });
            }

            document.getElementById('buscarProducto')?.addEventListener('input', filtrarProductos);
            document.getElementById('categoriaFilter')?.addEventListener('change', filtrarProductos);
            document.getElementById('limpiarCarrito')?.addEventListener('click', limpiarCarrito);
            document.getElementById('btnVentaContado')?.addEventListener('click', mostrarModalPago);
            document.getElementById('btnVentaCredito')?.addEventListener('click', mostrarModalCredito);
            document.getElementById('btnCotizacion')?.addEventListener('click', generarCotizacion);
            document.getElementById('confirmarPago')?.addEventListener('click', confirmarVentaContado);
            document.getElementById('confirmarCredito')?.addEventListener('click', confirmarVentaCredito);
            document.getElementById('btnPagoSimple')?.addEventListener('click', () => setModoPago('simple'));
            document.getElementById('btnPagoMixto')?.addEventListener('click', () => setModoPago('mixto'));
            document.getElementById('montoSimple')?.addEventListener('input', () => actualizarPagoSimple());

            document.querySelectorAll('#modoMixto .monto-pago-input').forEach(input => {
                input.addEventListener('input', () => actualizarPagoMixto());
            });

            document.getElementById('numPagos')?.addEventListener('input', calcularMontoPorPago);

            document.querySelectorAll('.producto-card').forEach(card => {
                card.addEventListener('click', async () => {
                    const id = card.dataset.id;
                    const nombre = card.dataset.nombre;
                    const stock = parseInt(card.dataset.stock);
                    const productoExistente = carrito.find(p => p.id == id);
                    if (productoExistente && productoExistente.cantidad + 1 > stock) {
                        Swal.fire('Stock insuficiente', `Solo hay ${stock} unidades disponibles`, 'warning');
                        return;
                    }
                    const result = await agregarProducto(id, 1);
                    if (result?.success) {
                        Swal.fire({ title: '¡Agregado!', text: `${nombre} agregado al carrito`, icon: 'success', timer: 1000, showConfirmButton: false, toast: true, position: 'top-end' });
                    }
                });
            });

            document.querySelectorAll('#modoMixto [data-requiere-referencia="1"]').forEach(card => {
                card.querySelector('.referencia-input')?.classList.remove('hidden');
            });

            document.addEventListener('DOMContentLoaded', () => {
                cargarCarrito();
                setModoPago('simple');
            });

            function escapeHtml(str) {
                if (!str) return '';
                return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : m === '>' ? '&gt;' : m);
            }

            function guardarCajaSeleccionada() {
                const select = document.getElementById('cajaActivaSelect');
                if (select) localStorage.setItem('cajaActivaSeleccionada', select.value);
            }
            async function mostrarConfirmacionVenta(pagos, totalVenta, subtotal, iva) {
                // Calcular total pagado (suma de los pagos ajustados)
                const totalPagado = pagos.reduce((sum, p) => sum + p.monto, 0);
                const cambio = totalPagado - totalVenta;

                // Crear HTML para el desglose
                let formasPagoHtml = '';
                for (let pago of pagos) {
                    const forma = formasPagoData.find(f => f.id == pago.forma_pago_id);
                    formasPagoHtml += `
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">${forma?.icono || '💰'}</span>
                                    <span class="font-medium">${forma?.nombre || 'Forma de pago'}</span>
                                </div>
                                <span class="font-bold text-green-600">$${pago.monto.toFixed(2)}</span>
                            </div>
                        `;
                }

                const confirmHtml = `
                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <h4 class="mb-2 font-bold text-gray-700">📊 Resumen de la venta</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">$${subtotal.toFixed(2)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">IVA (16%):</span>
                                        <span class="font-medium">$${iva.toFixed(2)}</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t border-gray-200">
                                        <span class="font-bold text-gray-800">Total a pagar:</span>
                                        <span class="font-bold text-indigo-600">$${totalVenta.toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-green-50 rounded-xl">
                                <h4 class="mb-2 font-bold text-green-800">💵 Formas de pago</h4>
                                <div class="space-y-1">
                                    ${formasPagoHtml}
                                    <div class="flex justify-between pt-2 mt-2 border-t border-green-200">
                                        <span class="font-bold text-gray-800">Total pagado:</span>
                                        <span class="font-bold text-green-600">$${totalPagado.toFixed(2)}</span>
                                    </div>
                                    ${cambio > 0 ? `
                                    <div class="flex justify-between pt-2">
                                        <span class="font-bold text-gray-800">💰 Cambio a devolver:</span>
                                        <span class="font-bold text-orange-600">$${cambio.toFixed(2)}</span>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;

                const { isConfirmed } = await Swal.fire({
                    title: '✅ Confirmar venta',
                    html: confirmHtml,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '✅ Confirmar venta',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    width: '500px'
                });

                return isConfirmed;
            }
        </script>
    @endpush
@endsection

<style>
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.3s ease-out;
    }

    /* SweetAlert responsivo */
    .swal2-popup.swal2-responsive {
        max-width: 95vw !important;
        width: auto !important;
        min-width: 300px !important;
    }

    @media (min-width: 640px) {
        .swal2-popup.swal2-responsive {
            max-width: 650px !important;
        }
    }

    @media (min-width: 768px) {
        .swal2-popup.swal2-responsive {
            max-width: 750px !important;
        }
    }
</style>