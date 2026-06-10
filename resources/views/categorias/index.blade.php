@extends('layouts.app')

@section('title', 'Categorías')
@section('page-title', 'Categorías')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $categorias->count() }} de {{ $categorias->total() }} categorías</span>
    </div>
    
    {{-- Exportar Excel: Solo Super Admin y Administrador --}}
    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('categorias.export') }}" 
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
        📥 Exportar Excel
    </a>
    @endif
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Categorías de productos</h2>
            <p class="mt-1 text-sm text-gray-500">Organiza tus productos por categorías</p>
        </div>
        
        {{-- Crear categoría: Solo Super Admin y Administrador --}}
        @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
        <a href="{{ route('categorias.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nueva categoría
        </a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Descripción</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Productos</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categorias as $categoria)
                <tr id="categoria-row-{{ $categoria->id }}" class="transition hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-slate-800">{{ $categoria->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($categoria->descripcion, 50) ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">{{ $categoria->productos_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span id="estado-{{ $categoria->id }}" class="text-sm {{ $categoria->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $categoria->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Ver detalle: Todos los roles --}}
                            <a href="{{ route('categorias.show', $categoria) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            
                            {{-- Editar: Solo Super Admin y Administrador --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                            <a href="{{ route('categorias.edit', $categoria) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            @endif
                            
                            {{-- Eliminar/Desactivar: Solo Super Admin y Administrador y sin productos asociados --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']) && $categoria->productos_count == 0)
                                @if($categoria->activo)
                                <button type="button"
                                    class="p-2 text-gray-400 btn-desactivar hover:text-red-600"
                                    data-id="{{ $categoria->id }}"
                                    data-nombre="{{ $categoria->nombre }}"
                                    title="Desactivar">🗑️</button>
                                @else
                                <button type="button"
                                    class="p-2 text-gray-400 btn-reactivar hover:text-green-600"
                                    data-id="{{ $categoria->id }}"
                                    data-nombre="{{ $categoria->nombre }}"
                                    title="Reactivar">✅</button>
                                @endif
                            @elseif($categoria->productos_count > 0)
                                <span class="p-2 text-gray-300 cursor-not-allowed" title="No se puede eliminar, tiene productos asociados">🔒</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">No hay categorías registradas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">{{ $categorias->links() }}</div>
</div>

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
        
        // ==================== DESACTIVAR CATEGORÍA ====================
        const desactivarBtns = document.querySelectorAll('.btn-desactivar');
        
        desactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Desactivar categoría?',
                    html: `Categoría: <strong>${nombre}</strong><br><br>La categoría quedará inactiva pero sus datos se conservarán.`,
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
                        const response = await axios.delete(`/categorias/${id}`);
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
                        const msg = error.response?.data?.message || 'Error al desactivar la categoría';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
        
        // ==================== REACTIVAR CATEGORÍA ====================
        const reactivarBtns = document.querySelectorAll('.btn-reactivar');
        
        reactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Reactivar categoría?',
                    html: `Categoría: <strong>${nombre}</strong><br><br>La categoría volverá a estar activa en el sistema.`,
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
                        const response = await axios.put(`/categorias/${id}/reactivar`);
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
                        const msg = error.response?.data?.message || 'Error al reactivar la categoría';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection