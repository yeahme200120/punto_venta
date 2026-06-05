@extends('layouts.app')

@section('title', 'Insumos')
@section('page-title', 'Insumos')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Insumos</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <span class="text-sm text-gray-400">Mostrando {{ $insumos->count() }} de {{ $insumos->total() }} insumos</span>
    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('insumos.export') }}" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">📥 Exportar Excel</a>
    @endif
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de insumos</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona tus materias primas e insumos</p>
        </div>
        <a href="{{ route('insumos.create') }}" class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">+ Nuevo insumo</a>
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
                        @if($insumo->activo)
                            <span class="text-sm text-green-600">● Activo</span>
                        @else
                            <span class="text-sm text-red-600">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('insumos.show', $insumo) }}" class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">
                                👁️
                            </a>
                            <a href="{{ route('insumos.edit', $insumo) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">
                                ✏️
                            </a>
                            <button type="button" onclick="toggleActivo({{ $insumo->id }}, {{ $insumo->activo ? 'true' : 'false' }}, '{{ $insumo->nombre }}')" 
                                    class="p-2 text-gray-400 transition hover:text-indigo-600" title="{{ $insumo->activo ? 'Desactivar' : 'Activar' }}">
                                {{ $insumo->activo ? '🔴' : '🟢' }}
                            </button>
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

<script>
function toggleActivo(id, activo, nombre) {
    const accion = activo ? 'desactivar' : 'activar';
    
    Swal.fire({
        title: `¿${accion === 'activar' ? 'Activar' : 'Desactivar'} insumo?`,
        text: `¿Estás seguro de ${accion} el insumo "${nombre}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/insumos/${id}/toggle-activo`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Estado actualizado',
                        text: data.message,
                        confirmButtonColor: '#4f46e5'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#4f46e5'
                    });
                }
            }).catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión',
                    confirmButtonColor: '#4f46e5'
                });
            });
        }
    });
}
</script>
@endsection