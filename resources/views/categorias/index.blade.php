@extends('layouts.app')

@section('title', 'Categorías')
@section('page-title', 'Categorías')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

{{-- Verificar permisos --}}
@php
    $puedeCrear = auth()->user()->can('crear_categorias');
    $puedeEditar = auth()->user()->can('editar_categorias');
    $puedeEliminar = auth()->user()->can('eliminar_categorias');
    $puedeExportar = auth()->user()->can('ver_categorias');
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $categorias->count() }} de {{ $categorias->total() }} categorías</span>
    </div>
    
    {{-- Exportar Excel: solo con permiso --}}
    @can('ver_categorias')
    <a href="{{ route('categorias.export') }}" 
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
        📥 Exportar Excel
    </a>
    @endcan
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Categorías de productos</h2>
            <p class="mt-1 text-sm text-gray-500">Organiza tus productos por categorías</p>
        </div>
        
        {{-- Crear categoría: solo con permiso --}}
        @can('crear_categorias')
        <a href="{{ route('categorias.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nueva categoría
        </a>
        @endcan
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
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($categoria->descripcion, 50) ?: '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                            {{ $categoria->productos_count ?? $categoria->productos->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span id="estado-{{ $categoria->id }}" class="text-sm {{ $categoria->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $categoria->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Ver detalle: todos con permiso de ver --}}
                            @can('ver_categorias')
                            <a href="{{ route('categorias.show', $categoria) }}" 
                               class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">👁️</a>
                            @endcan
                            
                            {{-- Editar: solo con permiso --}}
                            @can('editar_categorias')
                            <a href="{{ route('categorias.edit', $categoria) }}" 
                               class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">✏️</a>
                            @endcan
                            
                            {{-- Desactivar/Reactivar: solo con permiso --}}
                            @can('eliminar_categorias')
                                @php $productosCount = $categoria->productos_count ?? $categoria->productos->count(); @endphp
                                @if($productosCount == 0)
                                    @if($categoria->activo)
                                    <button type="button"
                                        class="p-2 text-gray-400 transition btn-desactivar hover:text-red-600"
                                        data-id="{{ $categoria->id }}"
                                        data-nombre="{{ $categoria->nombre }}"
                                        title="Desactivar">🗑️</button>
                                    @else
                                    <button type="button"
                                        class="p-2 text-gray-400 transition btn-reactivar hover:text-green-600"
                                        data-id="{{ $categoria->id }}"
                                        data-nombre="{{ $categoria->nombre }}"
                                        title="Reactivar">✅</button>
                                    @endif
                                @else
                                    <span class="p-2 text-gray-300 cursor-not-allowed" 
                                          title="No se puede modificar, tiene {{ $productosCount }} producto(s) asociado(s)">🔒</span>
                                @endif
                            @endcan
                            
                            {{-- Sin permisos --}}
                            @cannot('ver_categorias')
                                @cannot('editar_categorias')
                                    @cannot('eliminar_categorias')
                                        <span class="text-xs text-gray-400">Sin acceso</span>
                                    @endcannot
                                @endcannot
                            @endcannot
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

{{-- Script directo, no en @push --}}
<script>
    // Esperar a que Axios y Swal estén disponibles
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ [CATEGORIAS] DOM listo');
        console.log('   Axios:', typeof axios !== 'undefined' ? '✅' : '❌');
        console.log('   Swal:', typeof Swal !== 'undefined' ? '✅' : '❌');
        
        if (typeof axios === 'undefined') {
            console.error('❌ [CATEGORIAS] Axios no disponible');
            return;
        }
        
        // Configurar Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        
        // Verificar permisos
        const canDelete = @json(auth()->user()->can('eliminar_categorias'));
        const canEdit = @json(auth()->user()->can('editar_categorias'));
        console.log('🔑 [CATEGORIAS] Permisos - Editar:', canEdit, '| Eliminar:', canDelete);
        
        // ==================== DESACTIVAR ====================
        document.querySelectorAll('.btn-desactivar').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!canDelete) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso denegado',
                        text: 'No tienes permisos para desactivar categorías.',
                        confirmButtonColor: '#ef4444'
                    });
                    return;
                }
                
                const { id, nombre } = btn.dataset;
                
                const { isConfirmed } = await Swal.fire({
                    title: '¿Desactivar categoría?',
                    html: `Categoría: <strong>${nombre}</strong><br><br>La categoría quedará inactiva.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (!isConfirmed) return;
                
                Swal.fire({
                    title: 'Desactivando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                try {
                    const response = await axios.delete(`/categorias/${id}`);
                    
                    if (response.data?.success !== false) {
                        await Swal.fire({
                            icon: 'success',
                            title: '¡Desactivada!',
                            text: response.data?.message || `Categoría "${nombre}" desactivada.`,
                            confirmButtonColor: '#10b981',
                            timer: 2000
                        });
                        location.reload();
                    } else {
                        throw new Error(response.data?.message || 'Error al desactivar');
                    }
                } catch (error) {
                    console.error('❌ [CATEGORIAS] Error desactivar:', error);
                    
                    let msg = 'Error al desactivar la categoría';
                    if (error.response?.status === 403) msg = 'No tienes permisos.';
                    else if (error.response?.status === 404) msg = 'Categoría no encontrada.';
                    else if (error.response?.data?.message) msg = error.response.data.message;
                    
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        });
        
        // ==================== REACTIVAR ====================
        document.querySelectorAll('.btn-reactivar').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!canEdit) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso denegado',
                        text: 'No tienes permisos para reactivar categorías.',
                        confirmButtonColor: '#ef4444'
                    });
                    return;
                }
                
                const { id, nombre } = btn.dataset;
                
                const { isConfirmed } = await Swal.fire({
                    title: '¿Reactivar categoría?',
                    html: `Categoría: <strong>${nombre}</strong><br><br>Volverá a estar activa.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, reactivar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (!isConfirmed) return;
                
                Swal.fire({
                    title: 'Reactivando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                try {
                    const response = await axios.put(`/categorias/${id}/reactivar`);
                    
                    if (response.data?.success !== false) {
                        await Swal.fire({
                            icon: 'success',
                            title: '¡Reactivada!',
                            text: response.data?.message || `Categoría "${nombre}" reactivada.`,
                            confirmButtonColor: '#10b981',
                            timer: 2000
                        });
                        location.reload();
                    } else {
                        throw new Error(response.data?.message || 'Error al reactivar');
                    }
                } catch (error) {
                    console.error('❌ [CATEGORIAS] Error reactivar:', error);
                    
                    let msg = 'Error al reactivar la categoría';
                    if (error.response?.status === 403) msg = 'No tienes permisos.';
                    else if (error.response?.status === 404) msg = 'Categoría no encontrada.';
                    else if (error.response?.data?.message) msg = error.response.data.message;
                    
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        });
        
        console.log('✅ [CATEGORIAS] Listeners registrados');
    });
</script>
@endsection