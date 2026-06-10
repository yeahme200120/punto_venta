@extends('layouts.app')

@section('title', 'Producto: ' . $producto->nombre)
@section('page-title', $producto->nombre)
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('productos.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Productos
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">{{ $producto->nombre }}</span>
    </li>
@endsection

@section('content')

<div class="max-w-6xl mx-auto">
    <div class="p-8 mb-6 bg-white shadow-lg rounded-3xl">
        <div class="flex items-start justify-between pb-6 mb-6 border-b">
            <div class="flex gap-6">
                {{-- Galería de imágenes --}}
                <div class="w-48">
                    <div class="relative">
                        <div class="w-48 h-48 overflow-hidden bg-gray-100 border rounded-xl">
                            @if($producto->imagen_principal)
                                <img id="imagenPrincipal" src="{{ $producto->imagen_principal }}" class="object-cover w-full h-full"
                                    alt="{{ $producto->nombre }}">
                            @else
                                <div class="flex items-center justify-center w-full h-full text-6xl">📦</div>
                            @endif
                        </div>
                        @if($producto->imagenes->count() > 1)
                            <div class="flex gap-1 mt-2">
                                @foreach($producto->imagenes->take(3) as $img)
                                    <div class="w-10 h-10 overflow-hidden bg-gray-100 border rounded-lg cursor-pointer hover:opacity-80"
                                        onclick="document.getElementById('imagenPrincipal').src='{{ $img->url }}'">
                                        <img src="{{ $img->url }}" class="object-cover w-full h-full">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-slate-800">{{ $producto->nombre }}</h2>
                    <p class="mt-1 text-sm text-gray-500">SKU: {{ $producto->sku ?? '—' }} | CB:
                        {{ $producto->codigo_barras ?? '—' }}</p>
                    <div class="flex gap-2 mt-3">
                        @if($producto->empresa)
                            <span class="px-2 py-1 text-xs text-indigo-700 bg-indigo-100 rounded-full">🏢
                                {{ $producto->empresa->nombre }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                {{-- Editar: Solo Super Admin y Administrador --}}
                @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                <a href="{{ route('productos.edit', $producto) }}"
                    class="px-4 py-2 text-sm font-medium text-white transition shadow bg-amber-500 rounded-xl hover:bg-amber-600">✏️
                    Editar</a>
                @endif
                
                {{-- Activar/Desactivar: Solo Super Admin y Administrador --}}
                @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                <button id="btnToggleActivo"
                    data-id="{{ $producto->id }}"
                    data-activo="{{ $producto->activo ? 'true' : 'false' }}"
                    class="btn-toggle-activo px-4 py-2 {{ $producto->activo ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-xl transition font-medium shadow text-sm">
                    {{ $producto->activo ? '🔴 Desactivar' : '🟢 Activar' }}
                </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-5">
            <div class="p-4 text-center bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                <p id="stockValue" class="text-2xl font-bold {{ $producto->stock <= $producto->stock_minimo ? 'text-red-600' : 'text-indigo-600' }}">
                    {{ number_format($producto->stock, 2) }}
                </p>
                <p class="text-xs text-gray-500">Stock Actual</p>
            </div>
            <div class="p-4 text-center bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl">
                <p class="text-2xl font-bold text-green-600">${{ number_format($producto->precio_venta, 2) }}</p>
                <p class="text-xs text-gray-500">Precio Venta</p>
            </div>
            <div class="p-4 text-center bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl">
                <p class="text-2xl font-bold text-amber-600">${{ number_format($producto->costo_compra, 2) }}</p>
                <p class="text-xs text-gray-500">Costo Compra</p>
            </div>
            <div class="p-4 text-center bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl">
                <p class="text-2xl font-bold text-purple-600">{{ $producto->insumos->count() }}</p>
                <p class="text-xs text-gray-500">Insumos</p>
            </div>
            <div class="p-4 text-center bg-gradient-to-br from-cyan-50 to-blue-50 rounded-xl">
                <p class="text-2xl font-bold text-cyan-600">{{ $producto->relacionados->count() }}</p>
                <p class="text-xs text-gray-500">Relacionados</p>
            </div>
        </div>

        @if($producto->descripcion)
        <div class="mb-6">
            <h3 class="mb-2 text-lg font-bold">📝 Descripción</h3>
            <p class="text-gray-600">{{ $producto->descripcion }}</p>
        </div>
        @endif

        {{-- PRODUCTOS RELACIONADOS --}}
        @if($producto->relacionados->count() > 0)
        <div class="mb-6">
            <h3 class="mb-3 text-lg font-bold">🔗 Productos relacionados</h3>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
                @foreach($producto->relacionados as $rel)
                <a href="{{ route('productos.show', $rel) }}"
                    class="p-3 text-center transition border rounded-xl hover:bg-indigo-50">
                    <div class="mb-1 text-2xl">📦</div>
                    <p class="text-sm font-medium truncate">{{ $rel->nombre }}</p>
                    <p class="text-xs text-gray-500">${{ number_format($rel->precio_venta, 2) }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- INSUMOS --}}
        @if($producto->insumos->count() > 0)
        <div class="mb-6">
            <h3 class="mb-3 text-lg font-bold">🧱 Insumos</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($producto->insumos as $insumo)
                <span class="px-3 py-1 text-sm rounded-full bg-slate-100 text-slate-700">
                    {{ $insumo->nombre }} ({{ number_format($insumo->pivot->cantidad, 2) }}
                    {{ $insumo->unidad_medida }})
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- PROVEEDORES --}}
        @if($producto->proveedores->count() > 0)
        <div class="mb-6">
            <h3 class="mb-3 text-lg font-bold">🚚 Proveedores</h3>
            <div class="space-y-2">
                @foreach($producto->proveedores as $prov)
                <div class="flex items-center justify-between p-3 border rounded-xl">
                    <span>{{ $prov->nombre }}</span>
                    <span class="text-sm text-gray-500">${{ number_format($prov->pivot->precio_compra, 2) }} |
                        {{ $prov->pivot->tiempo_entrega_dias }} días</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ÚLTIMOS MOVIMIENTOS --}}
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <h3 class="mb-3 text-lg font-bold">📋 Últimos movimientos</h3>
        <div class="space-y-2">
            @forelse($producto->movimientos as $mov)
            <div class="flex items-center justify-between p-3 text-sm border rounded-xl">
                <div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $mov->tipo == 'entrada' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $mov->tipo == 'salida' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $mov->tipo == 'transferencia' ? 'bg-blue-100 text-blue-700' : '' }}">
                        {{ ucfirst($mov->tipo) }}
                    </span>
                    <span class="ml-2">{{ number_format($mov->cantidad, 2) }} und</span>
                </div>
                <span class="text-gray-400">{{ $mov->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @empty
            <p class="py-4 text-center text-gray-400">Sin movimientos registrados</p>
            @endforelse
        </div>
    </div>

    <div class="flex justify-between mt-6">
        <a href="{{ route('productos.index') }}"
            class="inline-flex items-center gap-2 px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
            ← Volver
        </a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.headers.common['Content-Type'] = 'application/json';
        
        // Función para mostrar Swal
        function showSwal(icon, title, message, reload = false) {
            Swal.fire({
                icon: icon,
                title: title,
                text: message,
                confirmButtonText: 'Cerrar'
            }).then(() => {
                if (reload) {
                    location.reload();
                }
            });
        }
        
        // ==================== TOGGLE ACTIVO/DESACTIVO ====================
        const btnToggle = document.getElementById('btnToggleActivo');
        
        if (btnToggle) {
            btnToggle.addEventListener('click', async () => {
                const id = btnToggle.dataset.id;
                const activo = btnToggle.dataset.activo === 'true';
                const accion = activo ? 'desactivar' : 'activar';
                
                const confirm = await Swal.fire({
                    title: `¿${accion === 'activar' ? 'Activar' : 'Desactivar'} producto?`,
                    html: `Producto: <strong>{{ $producto->nombre }}</strong><br><br>${activo ? 'El producto quedará inactivo.' : 'El producto volverá a estar activo.'}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: activo ? '#d33' : '#10b981',
                    confirmButtonText: `Sí, ${accion}`,
                    cancelButtonText: 'Cancelar'
                });
                
                if (confirm.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    try {
                        const response = await axios.post(`/productos/${id}/toggle-activo`);
                        const data = response.data;
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: accion === 'activar' ? 'Producto activado' : 'Producto desactivado',
                                text: data.message,
                                confirmButtonText: 'Cerrar'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            showSwal('error', 'Error', data.message);
                        }
                    } catch (error) {
                        const msg = error.response?.data?.message || 'Error al cambiar el estado del producto';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection