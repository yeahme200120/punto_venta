@extends('layouts.app')

@section('title', 'Productos')
@section('page-title', 'Productos')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Productos</span>
    </li>
@endsection

@section('content')

    {{-- Componente para mensajes flash con SweetAlert --}}
    <x-alert type="success" :message="session('success')" />
    <x-alert type="error" :message="session('error')" />

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-400">Mostrando {{ $productos->count() }} de {{ $productos->total() }}
                productos</span>
            @if($empresaActiva)
                <span class="px-2 py-1 text-xs text-indigo-700 bg-indigo-100 rounded-full">🏢
                    {{ $empresaActiva->nombre }}</span>
            @endif
            @if($sucursalActiva)
                <span class="px-2 py-1 text-xs rounded-full bg-cyan-100 text-cyan-700">📍 {{ $sucursalActiva->nombre }}</span>
            @endif
        </div>
        
        {{-- Exportar Excel: Solo usuarios con permiso exportar_productos --}}
        @can('exportar_productos')
            <a href="{{ route('productos.export') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
                📥 Exportar Excel
            </a>
        @endcan
    </div>

    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="flex items-center justify-between p-6 border-b">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Lista de productos</h2>
                <p class="mt-1 text-sm text-gray-500">Gestiona tu inventario</p>
            </div>
            
            {{-- Crear producto: Solo usuarios con permiso crear_productos --}}
            @can('crear_productos')
            <a href="{{ route('productos.create') }}"
                class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                + Nuevo producto
            </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Categoría</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Precio Venta</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Insumos</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($productos as $producto)
                        <tr id="producto-row-{{ $producto->id }}"
                            class="hover:bg-gray-50 transition {{ $producto->stock <= $producto->stock_minimo ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 overflow-hidden bg-gray-100 rounded-lg">
                                        @if($producto->imagen_principal)
                                            <img src="{{ $producto->imagen_principal }}" class="object-cover w-full h-full">
                                        @else
                                            <span class="text-lg">📦</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-medium text-slate-800">{{ $producto->nombre }}</span>
                                        @if($producto->sku)
                                        <p class="text-xs text-gray-400">SKU: {{ $producto->sku }}</p>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $producto->categoria->nombre ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span id="stock-{{ $producto->id }}"
                                    class="font-semibold {{ $producto->stock <= $producto->stock_minimo ? 'text-red-600' : 'text-slate-800' }}">
                                    {{ number_format($producto->stock, 2) }}
                                </span>
                                @if($producto->stock <= $producto->stock_minimo)
                                    <span class="block text-xs text-red-500">⚠️ Stock bajo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-center">${{ number_format($producto->precio_venta, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-center text-gray-500">{{ $producto->insumos->count() }}</td>
                            <td class="px-6 py-4 text-center">
                                <span id="estado-{{ $producto->id }}" class="text-sm {{ $producto->activo ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $producto->activo ? '● Activo' : '● Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Ver detalle: Todos los usuarios autenticados --}}
                                    @auth
                                    <a href="{{ route('productos.show', $producto) }}"
                                        class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">👁️</a>
                                    @endauth
                                    
                                    {{-- Editar: Solo usuarios con permiso editar_productos --}}
                                    @can('editar_productos')
                                    <a href="{{ route('productos.edit', $producto) }}"
                                        class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">✏️</a>
                                    @endcan
                                    
                                    {{-- Desactivar/Reactivar: Solo usuarios con permiso editar_productos --}}
                                    @can('editar_productos')
                                        @if($producto->activo)
                                        <button type="button"
                                            class="p-2 text-gray-400 transition btn-desactivar hover:text-red-600"
                                            data-id="{{ $producto->id }}"
                                            data-nombre="{{ $producto->nombre }}"
                                            title="Desactivar">🗑️</button>
                                        @else
                                        <button type="button"
                                            class="p-2 text-gray-400 transition btn-reactivar hover:text-green-600"
                                            data-id="{{ $producto->id }}"
                                            data-nombre="{{ $producto->nombre }}"
                                            title="Reactivar">✅</button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">No hay productos registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">{{ $productos->links() }}</div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['Accept'] = 'application/json';
        
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
        
        // ==================== DESACTIVAR PRODUCTO ====================
        const desactivarBtns = document.querySelectorAll('.btn-desactivar');
        
        desactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Desactivar producto?',
                    html: `Producto: <strong>${nombre}</strong><br><br>El producto quedará inactivo pero sus datos se conservarán.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (confirm.isConfirmed) {
                    Swal.fire({
                        title: 'Desactivando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    try {
                        const response = await axios.delete(`/productos/${id}`);
                        const data = response.data;
                        
                        Swal.fire({
                            icon: data.icon || 'success',
                            title: 'Desactivado',
                            text: data.message,
                            confirmButtonText: 'Cerrar'
                        }).then(() => {
                            location.reload();
                        });
                    } catch (error) {
                        const msg = error.response?.data?.message || 'Error al desactivar el producto';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
        
        // ==================== REACTIVAR PRODUCTO ====================
        const reactivarBtns = document.querySelectorAll('.btn-reactivar');
        
        reactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Reactivar producto?',
                    html: `Producto: <strong>${nombre}</strong><br><br>El producto volverá a estar activo en el sistema.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Sí, reactivar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (confirm.isConfirmed) {
                    Swal.fire({
                        title: 'Reactivando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    try {
                        const response = await axios.put(`/productos/${id}/reactivar`);
                        const data = response.data;
                        
                        Swal.fire({
                            icon: data.icon || 'success',
                            title: 'Reactivado',
                            text: data.message,
                            confirmButtonText: 'Cerrar'
                        }).then(() => {
                            location.reload();
                        });
                    } catch (error) {
                        const msg = error.response?.data?.message || 'Error al reactivar el producto';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
    });
</script>
@endpush