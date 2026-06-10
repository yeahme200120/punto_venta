@extends('layouts.app')

@section('title', 'Proveedores')
@section('page-title', 'Proveedores')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Proveedores</span>
    </li>
@endsection

@section('content')

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $proveedores->count() }} de {{ $proveedores->total() }} proveedores</span>
    </div>
    
    <div class="flex items-center gap-2">
        <form method="GET" action="{{ route('proveedores.index') }}" class="relative">
            <input type="text" name="search" placeholder="Buscar proveedor..." value="{{ request('search') }}"
                class="w-64 py-2 pl-10 pr-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </form>
        
        {{-- Exportar Excel: Solo Super Admin y Administrador --}}
        @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
        <a href="{{ route('proveedores.export') }}" 
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
            📥 Exportar Excel
        </a>
        @endif
    </div>
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de proveedores</h2>
            <p class="mt-1 text-sm text-gray-500">Administra los proveedores de la empresa</p>
        </div>
        
        {{-- Crear proveedor: Solo Super Admin y Administrador --}}
        @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
        <a href="{{ route('proveedores.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo proveedor
        </a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Proveedor</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">RFC</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Teléfono</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Correo</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Productos</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($proveedores as $proveedor)
                <tr id="proveedor-row-{{ $proveedor->id }}" class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-full shadow bg-gradient-to-br from-amber-500 to-orange-500">
                                {{ $proveedor->iniciales }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-800">{{ $proveedor->nombre }}</span>
                                @if($proveedor->direccion)
                                <p class="text-xs text-gray-400">{{ Str::limit($proveedor->direccion, 40) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm">{{ $proveedor->rfc ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $proveedor->telefono ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $proveedor->correo ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span id="productos-count-{{ $proveedor->id }}" class="px-2 py-1 text-xs rounded-full {{ $proveedor->productos->count() > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $proveedor->productos->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span id="estado-{{ $proveedor->id }}" class="text-sm {{ $proveedor->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $proveedor->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Ver detalle: Todos los roles --}}
                            <a href="{{ route('proveedores.show', $proveedor) }}" class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">👁️</a>
                            
                            {{-- Editar: Solo Super Admin y Administrador --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                            <a href="{{ route('proveedores.edit', $proveedor) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">✏️</a>
                            @endif
                            
                            {{-- Toggle Activo/Inactivo: Solo Super Admin y Administrador --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                                @if($proveedor->activo)
                                <button type="button"
                                    class="p-2 text-gray-400 transition btn-desactivar hover:text-red-600"
                                    data-id="{{ $proveedor->id }}"
                                    data-nombre="{{ $proveedor->nombre }}"
                                    data-productos="{{ $proveedor->productos->count() }}"
                                    title="Desactivar">
                                    🔴
                                </button>
                                @else
                                <button type="button"
                                    class="p-2 text-gray-400 transition btn-reactivar hover:text-green-600"
                                    data-id="{{ $proveedor->id }}"
                                    data-nombre="{{ $proveedor->nombre }}"
                                    title="Reactivar">
                                    🟢
                                </button>
                                @endif
                            @endif
                            
                            {{-- Eliminar: Solo Super Admin y Administrador y sin productos --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']) && $proveedor->productos->count() == 0)
                                @if($proveedor->activo)
                                <button type="button"
                                    class="p-2 text-gray-400 transition btn-eliminar hover:text-red-600"
                                    data-id="{{ $proveedor->id }}"
                                    data-nombre="{{ $proveedor->nombre }}"
                                    title="Eliminar">
                                    🗑️
                                </button>
                                @endif
                            @elseif($proveedor->productos->count() > 0)
                                <span class="p-2 text-gray-300 cursor-not-allowed" title="No se puede eliminar, tiene productos asociados">🔒</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">
                    No hay proveedores registrados
                    <div class="mt-2">
                        <a href="{{ route('proveedores.create') }}" class="text-indigo-600 hover:text-indigo-800">+ Crear primer proveedor</a>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $proveedores->links() }}
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
        
        // ==================== DESACTIVAR PROVEEDOR ====================
        const desactivarBtns = document.querySelectorAll('.btn-desactivar');
        
        desactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre, productos } = btn.dataset;
                
                if (parseInt(productos) > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se puede desactivar',
                        text: `El proveedor "${nombre}" tiene ${productos} producto(s) asociados. No se puede desactivar.`,
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                
                const confirm = await Swal.fire({
                    title: '¿Desactivar proveedor?',
                    html: `Proveedor: <strong>${nombre}</strong><br><br>El proveedor quedará inactivo pero sus datos se conservarán.`,
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
                        const response = await axios.post(`/proveedores/${id}/toggle-activo`);
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
                        const msg = error.response?.data?.message || 'Error al desactivar el proveedor';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
        
        // ==================== REACTIVAR PROVEEDOR ====================
        const reactivarBtns = document.querySelectorAll('.btn-reactivar');
        
        reactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Reactivar proveedor?',
                    html: `Proveedor: <strong>${nombre}</strong><br><br>El proveedor volverá a estar activo en el sistema.`,
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
                        const response = await axios.post(`/proveedores/${id}/toggle-activo`);
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
                        const msg = error.response?.data?.message || 'Error al reactivar el proveedor';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
        
        // ==================== ELIMINAR PROVEEDOR (FÍSICAMENTE) ====================
        const eliminarBtns = document.querySelectorAll('.btn-eliminar');
        
        eliminarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Eliminar proveedor?',
                    html: `Proveedor: <strong>${nombre}</strong><br><br>Esta acción eliminará permanentemente al proveedor. No se puede deshacer.`,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (confirm.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    try {
                        const response = await axios.delete(`/proveedores/${id}`);
                        const data = response.data;
                        
                        Swal.fire({
                            icon: data.icon || 'success',
                            title: 'Eliminado',
                            text: data.message,
                            confirmButtonText: 'Cerrar'
                        }).then(() => {
                            location.reload();
                        });
                    } catch (error) {
                        const msg = error.response?.data?.message || 'Error al eliminar el proveedor';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection