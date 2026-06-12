@extends('layouts.app')

@section('title', 'Insumos')
@section('page-title', 'Insumos')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Insumos</span></li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

{{-- Verificar permisos para mostrar botones --}}
@php
    $puedeCrear = auth()->user()->can('crear_insumos');
    $puedeEditar = auth()->user()->can('editar_insumos');
    $puedeEliminar = auth()->user()->can('eliminar_insumos');
    $puedeExportar = auth()->user()->can('ver_insumos');
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <span class="text-sm text-gray-400">Mostrando {{ $insumos->count() }} de {{ $insumos->total() }} insumos</span>
    
    <div class="flex gap-2">
        @can('ver_insumos')
        <a href="{{ route('insumos.export') }}" 
           class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
            📥 Exportar Excel
        </a>
        @endcan
        
        @can('crear_insumos')
        <a href="{{ route('insumos.create') }}" 
           class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo insumo
        </a>
        @endcan
    </div>
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de insumos</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona tus materias primas e insumos</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Insumo</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Unidad</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Costo Unitario</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Productos</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($insumos as $insumo)
                <tr class="hover:bg-gray-50 transition {{ $insumo->stock_bajo ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm text-indigo-600">{{ $insumo->codigo ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <span class="font-medium text-slate-800">{{ $insumo->nombre }}</span>
                            @if($insumo->descripcion)
                            <p class="text-xs text-gray-400">{{ Str::limit($insumo->descripcion, 40) }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($insumo->unidadMedida)
                            {{ $insumo->unidadMedida->clave }} - {{ $insumo->unidadMedida->nombre }}
                        @else
                            {{ $insumo->unidad_medida ?? '—' }}
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="font-semibold {{ $insumo->stock_bajo ? 'text-red-600' : 'text-slate-800' }}">
                            {{ number_format($insumo->stock, 2) }}
                        </span>
                        @if($insumo->stock_bajo)
                            <span class="block text-xs text-red-500">⚠️ Stock bajo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-medium text-right">
                        ${{ number_format($insumo->costo_unitario, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs rounded-full {{ $insumo->productos->count() > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $insumo->productos->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm {{ $insumo->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $insumo->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Ver: todos los que pueden ver insumos --}}
                            @can('ver_insumos')
                            <a href="{{ route('insumos.show', $insumo) }}" 
                               class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">
                                👁️
                            </a>
                            @endcan
                            
                            {{-- Editar: solo quien tiene permiso --}}
                            @can('editar_insumos')
                            <a href="{{ route('insumos.edit', $insumo) }}" 
                               class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">
                                ✏️
                            </a>
                            @endcan
                            
                            {{-- Activar/Desactivar: solo quien tiene permiso --}}
                            @can('editar_insumos')
                            <button type="button" 
                                    onclick="toggleActivoIns({{ $insumo->id }}, '{{ addslashes($insumo->nombre) }}')" 
                                    class="p-2 text-gray-400 transition hover:text-indigo-600" 
                                    title="{{ $insumo->activo ? 'Desactivar' : 'Activar' }}">
                                {{ $insumo->activo ? '🔴' : '🟢' }}
                            </button>
                            @endcan
                            
                            {{-- Sin permisos de edición --}}
                            @cannot('editar_insumos')
                                @cannot('ver_insumos')
                                    <span class="text-xs text-gray-400">Sin acceso</span>
                                @endcannot
                            @endcannot
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        No hay insumos registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t">
        {{ $insumos->links() }}
    </div>
</div>

{{-- ✅ SCRIPT DIRECTO, no dentro de @push --}}
<script>
    // Esperar a que Axios y Swal estén disponibles
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ [INSUMOS] DOM listo, verificando Axios y Swal...');
        console.log('   Axios:', typeof axios !== 'undefined' ? '✅' : '❌');
        console.log('   Swal:', typeof Swal !== 'undefined' ? '✅' : '❌');
        
        if (typeof axios === 'undefined') {
            console.error('❌ [INSUMOS] Axios no está disponible');
            return;
        }
        
        // Configurar Axios (ya configurado globalmente, pero aseguramos)
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        axios.defaults.headers.common['Accept'] = 'application/json';
        
        console.log('✅ [INSUMOS] Axios configurado');
    });
    
    // Verificar permiso desde Blade
    const canEditInsumos = @json(auth()->user()->can('editar_insumos'));
    console.log('🔑 [INSUMOS] canEditInsumos:', canEditInsumos);

    /**
     * Activar/Desactivar insumo
     */
    async function toggleActivoIns(id, nombre) {
        console.log('📂 [INSUMOS] toggleActivoIns llamado:', { id, nombre });
        
        // Verificar que Axios existe
        if (typeof axios === 'undefined') {
            console.error('❌ [INSUMOS] Axios no está definido');
            alert('Error: Axios no está disponible. Recarga la página.');
            return;
        }
        
        // Verificar permiso
        if (!canEditInsumos) {
            Swal.fire({
                icon: 'error',
                title: 'Acceso denegado',
                text: 'No tienes permisos para modificar insumos.',
                confirmButtonColor: '#ef4444'
            });
            return;
        }
        
        // Confirmar
        const { isConfirmed } = await Swal.fire({
            title: '¿Cambiar estado?',
            html: `¿Estás seguro de cambiar el estado de <strong>"${nombre}"</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        });
        
        if (!isConfirmed) return;
        
        // Loading
        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        try {
            console.log('📤 [INSUMOS] Enviando POST a:', `/insumos/${id}/toggle-activo`);
            
            const response = await axios.post(`/insumos/${id}/toggle-activo`);
            
            console.log('📥 [INSUMOS] Respuesta:', response.data);
            
            if (response.data && response.data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    html: response.data.message || 'Estado actualizado',
                    confirmButtonColor: '#10b981',
                    timer: 2000
                });
                location.reload();
            } else {
                throw new Error(response.data.message || 'Error desconocido');
            }
        } catch (error) {
            console.error('❌ [INSUMOS] Error:', error);
            
            let errorMessage = 'Ocurrió un error al cambiar el estado';
            
            if (error.response) {
                console.log('   Status:', error.response.status);
                console.log('   Data:', error.response.data);
                
                if (error.response.status === 403) {
                    errorMessage = 'No tienes permisos para realizar esta acción';
                } else if (error.response.status === 404) {
                    errorMessage = 'Insumo no encontrado';
                } else if (error.response.data?.message) {
                    errorMessage = error.response.data.message;
                }
            } else if (error.request) {
                errorMessage = 'Error de conexión. Verifica tu internet.';
            }
            
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                confirmButtonColor: '#ef4444'
            });
        }
    }
    
    console.log('✅ [INSUMOS] Funciones listas: toggleActivoIns');
</script>
@endsection