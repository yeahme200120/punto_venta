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
    @if(!isset($cajaAbierta) || !$cajaAbierta)
        <div class="flex items-center justify-between p-4 mb-4 border-l-4 border-yellow-400 bg-yellow-50 rounded-r-xl">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="font-medium text-yellow-800">No hay una caja abierta</p>
                    <p class="text-sm text-yellow-700">Para realizar ventas, debes abrir una caja primero.</p>
                </div>
            </div>
            @can('abrir_caja')
                <a href="{{ route('cajas.apertura') }}"
                    class="px-4 py-2 text-sm font-medium text-white transition bg-yellow-500 rounded-lg hover:bg-yellow-600">
                    🔓 Abrir caja
                </a>
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

    {{-- Modal de pago con cards por cada forma de pago --}}
    {{-- Modal de pago con cards por cada forma de pago --}}
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

            {{-- Checkbox para solicitar factura --}}
            <div class="flex items-center justify-between p-3 mb-4 bg-yellow-50 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">¿Desea factura?</p>
                        <p class="text-xs text-yellow-600">Se agregará el 16% de IVA al total</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="incluirFactura" class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                    </div>
                </label>
            </div>

            {{-- Resumen de la venta --}}
            <div
                class="grid grid-cols-1 gap-4 p-4 mb-6 bg-gradient-to-r from-indigo-50 to-cyan-50 rounded-xl md:grid-cols-2 lg:grid-cols-4">
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
                    <span class="text-sm text-gray-500">Total pagado</span>
                    <p id="totalPagado" class="text-2xl font-bold text-green-600">$0.00</p>
                </div>
            </div>

            {{-- Métodos de pago disponibles --}}
            <div class="mb-4">
                <h4 class="mb-3 text-sm font-semibold text-gray-500 uppercase">Métodos de pago disponibles</h4>
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
            </div>

            {{-- Resumen de pagos y cambio --}}
            <div id="resumenPagosActual" class="mt-2"></div>

            {{-- Cambio por método de pago --}}
            <div id="cambioPorMetodo" class="hidden p-3 mt-3 bg-yellow-50 rounded-xl">
                <p class="mb-2 text-sm font-semibold text-yellow-800">💰 Cambio a devolver por método:</p>
                <div id="cambioDetalle" class="space-y-1"></div>
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
            let incluirIva = false;

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
                const iva = incluirIva ? Math.round(subtotalRedondeado * 0.16 * 100) / 100 : 0;
                const total = Math.round((subtotalRedondeado + iva) * 100) / 100;

                container.innerHTML = html;
                document.getElementById('subtotal').innerHTML = `$${subtotalRedondeado.toFixed(2)}`;
                document.getElementById('iva').innerHTML = `$${iva.toFixed(2)}`;
                document.getElementById('total').innerHTML = `$${total.toFixed(2)}`;
            }

            // ==================== PAGOS MIXTOS ====================

            function actualizarTotalesPagos() {
                let totalPagado = 0;
                const pagosDetalle = [];
                const pagosTemp = [];

                document.querySelectorAll('.pago-card').forEach(card => {
                    let monto = parseFloat(card.querySelector('.monto-pago-input').value) || 0;
                    monto = Math.round(monto * 100) / 100;
                    const formaNombre = card.querySelector('.font-medium').innerText;
                    const formaId = card.dataset.formaId;
                    if (monto > 0) {
                        pagosDetalle.push({ nombre: formaNombre, monto: monto });
                        pagosTemp.push({ forma_pago_id: formaId, nombre: formaNombre, monto: monto });
                        totalPagado += monto;
                    }
                });

                totalPagado = Math.round(totalPagado * 100) / 100;

                const subtotal = Math.round(carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0) * 100) / 100;
                const iva = incluirIva ? Math.round(subtotal * 0.16 * 100) / 100 : 0;
                const totalVenta = Math.round((subtotal + iva) * 100) / 100;
                const sobrante = Math.round((totalPagado - totalVenta) * 100) / 100;

                // Actualizar resumen básico
                document.getElementById('subtotalModal').innerHTML = `$${subtotal.toFixed(2)}`;
                document.getElementById('ivaModal').innerHTML = `$${iva.toFixed(2)}`;
                document.getElementById('totalPagar').innerHTML = `$${totalVenta.toFixed(2)}`;
                document.getElementById('totalPagado').innerHTML = `$${totalPagado.toFixed(2)}`;

                // Calcular cambio por método
                const { cambioDistribuido, sobrante: sobranteCalculado } = calcularCambioPorMetodo(pagosTemp, totalVenta);

                const cambioContainer = document.getElementById('cambioPorMetodo');
                const cambioDetalle = document.getElementById('cambioDetalle');

                if (sobrante > 0 && cambioDistribuido.length > 0) {
                    cambioContainer.classList.remove('hidden');
                    let cambioHtml = '';
                    cambioDistribuido.forEach(item => {
                        cambioHtml += `
                        <div class="flex items-center justify-between p-2 text-sm bg-white rounded-lg">
                            <span>💰 ${item.nombre}</span>
                            <div class="text-right">
                                <span class="mr-2 text-gray-400 line-through">$${item.monto_original.toFixed(2)}</span>
                                <span class="font-bold text-green-600">→ $${item.monto_ajustado.toFixed(2)}</span>
                                <span class="ml-2 text-yellow-600">(Cambio: $${item.cambio.toFixed(2)})</span>
                            </div>
                        </div>
                    `;
                    });
                    cambioDetalle.innerHTML = cambioHtml;
                } else {
                    cambioContainer.classList.add('hidden');
                }

                return { totalPagado, totalVenta, sobrante, pagosDetalle, subtotal, iva, cambioDistribuido };
            }

            function inicializarEventosPagos() {
                // Checkbox de factura
                const facturaCheckbox = document.getElementById('incluirFactura');
                if (facturaCheckbox) {
                    facturaCheckbox.addEventListener('change', function () {
                        incluirIva = this.checked;
                        actualizarCarrito();
                        actualizarTotalesPagos();
                    });
                }

                document.querySelectorAll('.pago-card').forEach(card => {
                    const montoInput = card.querySelector('.monto-pago-input');
                    const referenciaInput = card.querySelector('.referencia-input');
                    const requiereRef = card.dataset.requiereReferencia === '1';
                    if (requiereRef) referenciaInput.classList.remove('hidden');
                    montoInput.addEventListener('input', function () {
                        let value = parseFloat(this.value) || 0;
                        if (value < 0) this.value = 0;
                        actualizarTotalesPagos();
                    });
                    montoInput.addEventListener('keydown', function (e) {
                        if (e.key === '-' || e.key === 'e') e.preventDefault();
                    });
                });
            }

            function mostrarModalPago() {
                if (carrito.length === 0) {
                    Swal.fire('Carrito vacío', 'Agrega productos al carrito', 'warning');
                    return;
                }
                const facturaCheckbox = document.getElementById('incluirFactura');
                if (facturaCheckbox) {
                    facturaCheckbox.checked = incluirIva;
                }
                document.querySelectorAll('.monto-pago-input').forEach(input => input.value = '0');
                actualizarTotalesPagos();
                document.getElementById('modalPago').classList.remove('hidden');
                document.getElementById('modalPago').classList.add('flex');
            }

            function cerrarModalPago() {
                document.getElementById('modalPago').classList.add('hidden');
                document.getElementById('modalPago').classList.remove('flex');
            }

            async function confirmarVentaContado() {
    const pagos = [];
    let error = false;

    document.querySelectorAll('.pago-card').forEach(card => {
        const formaPagoId = card.dataset.formaId;
        let monto = parseFloat(card.querySelector('.monto-pago-input').value) || 0;
        monto = Math.round(monto * 100) / 100;
        const referencia = card.querySelector('.referencia-input').value;

        if (monto > 0) {
            if (!formaPagoId) {
                Swal.fire('Error', 'Forma de pago no válida', 'warning');
                error = true;
                return;
            }
            pagos.push({ 
                forma_pago_id: formaPagoId, 
                monto: monto, 
                referencia: referencia || null,
                nombre: formasPagoData.find(f => f.id == formaPagoId)?.nombre || 'Desconocido'
            });
        }
    });

    if (error) return;
    if (pagos.length === 0) {
        Swal.fire('Error', 'Debes ingresar al menos un monto de pago', 'warning');
        return;
    }

    const subtotal = Math.round(carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0) * 100) / 100;
    const iva = incluirIva ? Math.round(subtotal * 0.16 * 100) / 100 : 0;
    const totalVenta = Math.round((subtotal + iva) * 100) / 100;
    const totalPagos = Math.round(pagos.reduce((sum, p) => sum + p.monto, 0) * 100) / 100;

    if (totalPagos < totalVenta - 0.01) {
        const faltante = Math.round((totalVenta - totalPagos) * 100) / 100;
        Swal.fire({
            title: '⚠️ Pago insuficiente',
            html: `<div class="text-center">
                <p class="text-lg font-semibold text-red-700">Faltante: $${faltante.toFixed(2)}</p>
                <p class="mt-2 text-sm text-gray-500">El total pagado ($${totalPagos.toFixed(2)}) es menor al total de la venta ($${totalVenta.toFixed(2)}).</p>
            </div>`,
            icon: 'warning',
            confirmButtonText: 'Ajustar pagos',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    // Calcular cambio distribuido
    const { cambioDistribuido } = calcularCambioPorMetodo(pagos, totalVenta);
    
    // Aplicar cambio a los pagos
    let pagosFinales = [...pagos];
    for (let cambio of cambioDistribuido) {
        const index = pagosFinales.findIndex(p => p.forma_pago_id == cambio.forma_pago_id);
        if (index !== -1) {
            pagosFinales[index].monto = cambio.monto_ajustado;
        }
    }
    pagosFinales = pagosFinales.filter(p => p.monto > 0);

    // Construir resumen
    let resumenHtml = `
        <div class="text-left">
            <div class="p-4 mb-4 ${totalPagos > totalVenta ? 'bg-green-50' : 'bg-gray-50'} rounded-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="text-xl font-bold text-indigo-600">$${subtotal.toFixed(2)}</span>
                </div>
                ${incluirIva ? `
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600">IVA (16%):</span>
                    <span class="text-xl font-bold text-indigo-600">$${iva.toFixed(2)}</span>
                </div>
                ` : ''}
                <div class="flex items-center justify-between pt-2 border-t">
                    <span class="text-gray-600">Total de la venta:</span>
                    <span class="text-xl font-bold text-indigo-600">$${totalVenta.toFixed(2)}</span>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-gray-600">Total pagado:</span>
                    <span class="text-xl font-bold text-green-600">$${totalPagos.toFixed(2)}</span>
                </div>
                ${cambioDistribuido.length > 0 ? `
                <div class="pt-2 mt-3 border-t border-green-200">
                    <p class="mb-2 text-sm font-semibold text-green-700">💰 Cambio a devolver:</p>
                    ${cambioDistribuido.map(c => `
                        <div class="flex items-center justify-between text-sm">
                            <span>${c.nombre}:</span>
                            <span class="font-bold text-green-600">$${c.cambio.toFixed(2)}</span>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
            </div>
            <div class="space-y-2">
                <p class="mb-2 text-sm font-semibold text-gray-600">Formas de pago aplicadas:</p>
                ${pagosFinales.map(pago => `
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                        <span>${pago.nombre}</span>
                        <span class="font-bold text-indigo-600">$${pago.monto.toFixed(2)}</span>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    const confirm = await Swal.fire({
        title: totalPagos > totalVenta ? '💰 Confirmar venta con cambio' : '✅ Confirmar venta',
        html: resumenHtml,
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: totalPagos > totalVenta ? '✅ Cobrar y dar cambio' : '✅ Cobrar y finalizar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        width: '550px'
    });

    if (confirm.isConfirmed) {
        try {
            const response = await axios.post('{{ route("ventas.contado.store") }}', {
                items: carrito,
                pagos: pagosFinales,
                incluir_iva: incluirIva,
                cambio_detalle: cambioDistribuido,
                observaciones: document.getElementById('observacionesVenta')?.value
            });

            if (response.data.success) {
                Swal.fire({
                    title: '🎉 ¡Venta exitosa!',
                    html: `
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-800">${response.data.folio}</p>
                            <div class="p-4 mt-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-gray-500">Total:</span>
                                    <span class="text-xl font-bold text-indigo-600">$${totalVenta.toFixed(2)}</span>
                                </div>
                                ${cambioDistribuido.length > 0 ? `
                                <div class="pt-2 mt-2 border-t border-green-200">
                                    <p class="text-sm font-semibold text-green-600">💰 Cambio devuelto:</p>
                                    ${cambioDistribuido.map(c => `
                                        <div class="flex items-center justify-between mt-1 text-sm">
                                            <span>${c.nombre}:</span>
                                            <span class="font-bold text-green-600">$${c.cambio.toFixed(2)}</span>
                                        </div>
                                    `).join('')}
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: '🧾 Imprimir ticket',
                    confirmButtonColor: '#4f46e5',
                    showCancelButton: true,
                    cancelButtonText: 'Nueva venta',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(`/ventas/${response.data.venta_id}/ticket`, '_blank');
                    }
                    carrito = [];
                    actualizarCarrito();
                    window.dispatchEvent(new CustomEvent('carrito-actualizado'));
                    cerrarModalPago();
                });
            }
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Error al registrar venta', 'error');
        }
    }
}

            // ==================== VENTA CRÉDITO ====================

            function mostrarModalCredito() {
                if (carrito.length === 0) {
                    Swal.fire('Carrito vacío', 'Agrega productos al carrito', 'warning');
                    return;
                }
                const subtotal = Math.round(carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0) * 100) / 100;
                const iva = incluirIva ? Math.round(subtotal * 0.16 * 100) / 100 : 0;
                const total = subtotal + iva;
                document.getElementById('totalCredito').innerText = total.toFixed(2);
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
                const subtotal = Math.round(carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0) * 100) / 100;
                const iva = incluirIva ? Math.round(subtotal * 0.16 * 100) / 100 : 0;
                const total = subtotal + iva;
                const numPagos = parseInt(document.getElementById('numPagos').value) || 1;
                const montoPorPago = total / numPagos;
                document.getElementById('montoPorPago').innerHTML = `$${montoPorPago.toFixed(2)}`;
            }

            document.getElementById('numPagos')?.addEventListener('input', calcularMontoPorPago);

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
                            incluir_iva: incluirIva
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
                                                                                                <select id="clienteCotizacion" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                                                                                    <option value="">Cliente mostrador</option>
                                                                                                    @foreach($clientes as $cliente)
                                                                                                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="mb-3">
                                                                                                <label class="block mb-1 text-sm font-medium text-gray-700">Días de validez</label>
                                                                                                <input type="number" id="diasValidez" value="7" min="1" max="90" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                                                            </div>
                                                                                            <div class="mb-3">
                                                                                                <label class="block mb-1 text-sm font-medium text-gray-700">Observaciones</label>
                                                                                                <textarea id="observaciones" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                                                                                            </div>
                                                                                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: '📄 Generar Cotización',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#f97316',
                    preConfirm: () => ({
                        clienteId: document.getElementById('clienteCotizacion').value,
                        diasValidez: document.getElementById('diasValidez').value,
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
                                text: `Folio: ${response.data.folio}`,
                                icon: 'success',
                                confirmButtonText: 'Ver PDF',
                                confirmButtonColor: '#f97316'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open(`/cotizaciones/${response.data.id}/pdf`, '_blank');
                                }
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
                    if (result && result.success) {
                        Swal.fire({
                            title: '¡Agregado!',
                            text: `${nombre} agregado al carrito`,
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }
                });
            });

            document.getElementById('limpiarCarrito')?.addEventListener('click', limpiarCarrito);
            document.getElementById('btnVentaContado')?.addEventListener('click', mostrarModalPago);
            document.getElementById('btnVentaCredito')?.addEventListener('click', mostrarModalCredito);
            document.getElementById('btnCotizacion')?.addEventListener('click', generarCotizacion);
            document.getElementById('confirmarPago')?.addEventListener('click', confirmarVentaContado);
            document.getElementById('confirmarCredito')?.addEventListener('click', confirmarVentaCredito);

            document.addEventListener('DOMContentLoaded', () => {
                cargarCarrito();
                inicializarEventosPagos();
            });

            function escapeHtml(str) {
                if (!str) return '';
                return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : m === '>' ? '&gt;' : m);
            }
            function calcularCambioPorMetodo(pagos, totalVenta) {
                let totalPagado = pagos.reduce((sum, p) => sum + p.monto, 0);
                let sobrante = Math.round((totalPagado - totalVenta) * 100) / 100;

                if (sobrante <= 0) return { cambioDistribuido: [], sobrante: 0 };

                // Ordenar pagos de mayor a menor para facilitar distribución del cambio
                let pagosOrdenados = [...pagos].sort((a, b) => b.monto - a.monto);
                let cambioRestante = sobrante;
                let cambioDistribuido = [];

                for (let pago of pagosOrdenados) {
                    if (cambioRestante <= 0) break;

                    let montoOriginal = pago.monto;
                    let montoAjustado = montoOriginal;
                    let cambioDeEsteMetodo = 0;

                    // Intentar tomar el cambio de este método de pago
                    if (montoOriginal >= cambioRestante) {
                        cambioDeEsteMetodo = cambioRestante;
                        montoAjustado = Math.round((montoOriginal - cambioRestante) * 100) / 100;
                        cambioRestante = 0;
                    } else {
                        cambioDeEsteMetodo = montoOriginal;
                        montoAjustado = 0;
                        cambioRestante = Math.round((cambioRestante - montoOriginal) * 100) / 100;
                    }

                    if (cambioDeEsteMetodo > 0) {
                        cambioDistribuido.push({
                            forma_pago_id: pago.forma_pago_id,
                            nombre: pago.nombre || formasPagoData.find(f => f.id == pago.forma_pago_id)?.nombre || 'Desconocido',
                            monto_original: montoOriginal,
                            monto_ajustado: montoAjustado,
                            cambio: cambioDeEsteMetodo
                        });
                    }
                }

                return { cambioDistribuido, sobrante };
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

    /* Scroll suave para el contenido */
    .swal2-html-container::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .swal2-html-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .swal2-html-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
</style>