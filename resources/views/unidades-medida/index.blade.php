@extends('layouts.app')

@section('title', 'Unidades de Medida')
@section('page-title', 'Unidades de Medida')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Unidades de Medida</span>
    </li>
@endsection

@section('content')

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-400">Mostrando {{ $unidades->count() }} de {{ $unidades->total() }} unidades</span>
    </div>
    
    {{-- Crear unidad: Solo Super Admin y Administrador --}}
    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('unidades-medida.create') }}" 
       class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
        + Nueva unidad
    </a>
    @endif
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold text-slate-800">Catálogo de unidades de medida</h2>
        <p class="mt-1 text-sm text-gray-500">Gestiona las unidades de medida para insumos y productos</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Clave</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Símbolo</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Insumos</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($unidades as $unidad)
                <tr id="unidad-row-{{ $unidad->id }}" class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <span class="font-mono font-medium text-indigo-600">{{ $unidad->clave }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <span class="font-medium text-slate-800">{{ $unidad->nombre }}</span>
                            @if($unidad->descripcion)
                            <p class="text-xs text-gray-400">{{ Str::limit($unidad->descripcion, 50) }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-500">{{ $unidad->tipo }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 font-mono text-xs bg-gray-100 rounded">{{ $unidad->simbolo ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-center text-gray-500">
                        <span id="insumos-count-{{ $unidad->id }}" class="px-2 py-1 rounded-full {{ $unidad->insumos->count() > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $unidad->insumos->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span id="estado-{{ $unidad->id }}" class="text-sm {{ $unidad->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $unidad->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Editar: Solo Super Admin y Administrador --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                            <a href="{{ route('unidades-medida.edit', $unidad) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">
                                ✏️
                            </a>
                            @endif
                            
                            {{-- Desactivar/Reactivar: Solo Super Admin y Administrador --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                                @if($unidad->activo)
                                    @if($unidad->insumos->count() > 0)
                                        <span class="p-2 text-gray-300 cursor-not-allowed" title="No se puede desactivar, tiene insumos asociados">🔒</span>
                                    @else
                                    <button type="button"
                                        class="p-2 text-gray-400 transition btn-desactivar hover:text-red-600"
                                        data-id="{{ $unidad->id }}"
                                        data-nombre="{{ $unidad->nombre }}"
                                        data-insumos="{{ $unidad->insumos->count() }}"
                                        title="Desactivar">
                                        🗑️
                                    </button>
                                    @endif
                                @else
                                <button type="button"
                                    class="p-2 text-gray-400 transition btn-reactivar hover:text-green-600"
                                    data-id="{{ $unidad->id }}"
                                    data-nombre="{{ $unidad->nombre }}"
                                    title="Reactivar">
                                    ✅
                                </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </td>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No hay unidades de medida registradas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t">
        {{ $unidades->links() }}
    </div>
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
        
        // ==================== DESACTIVAR UNIDAD ====================
        const desactivarBtns = document.querySelectorAll('.btn-desactivar');
        
        desactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre, insumos } = btn.dataset;
                
                if (parseInt(insumos) > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se puede desactivar',
                        text: `La unidad "${nombre}" tiene ${insumos} insumo(s) asociados. No se puede desactivar.`,
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                
                const confirm = await Swal.fire({
                    title: '¿Desactivar unidad?',
                    html: `Unidad: <strong>${nombre}</strong><br><br>La unidad quedará inactiva pero sus datos se conservarán.`,
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
                        const response = await axios.delete(`/unidades-medida/${id}`);
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
                        const msg = error.response?.data?.message || 'Error al desactivar la unidad';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
        
        // ==================== REACTIVAR UNIDAD ====================
        const reactivarBtns = document.querySelectorAll('.btn-reactivar');
        
        reactivarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, nombre } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Reactivar unidad?',
                    html: `Unidad: <strong>${nombre}</strong><br><br>La unidad volverá a estar activa en el sistema.`,
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
                        const response = await axios.put(`/unidades-medida/${id}/reactivar`);
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
                        const msg = error.response?.data?.message || 'Error al reactivar la unidad';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection