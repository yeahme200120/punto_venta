{{-- resources/views/unidades-medida/index.blade.php --}}
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
    <a href="{{ route('unidades-medida.create') }}" class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
        + Nueva unidad
    </a>
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
                <tr class="transition hover:bg-gray-50">
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
                        <span class="px-2 py-1 rounded-full {{ $unidad->insumos->count() > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $unidad->insumos->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($unidad->activo)
                            <span class="text-sm text-green-600">● Activo</span>
                        @else
                            <span class="text-sm text-red-600">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('unidades-medida.edit', $unidad) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">
                                ✏️
                            </a>
                            <button type="button" onclick="eliminarUnidad({{ $unidad->id }}, '{{ $unidad->nombre }}', {{ $unidad->insumos->count() }})" 
                                    class="p-2 text-gray-400 transition hover:text-red-600" title="Eliminar">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
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

<script>
function eliminarUnidad(id, nombre, insumosCount) {
    if (insumosCount > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No se puede eliminar',
            text: `La unidad "${nombre}" tiene ${insumosCount} insumo(s) asociados. No se puede eliminar.`,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    Swal.fire({
        title: '¿Eliminar unidad?',
        text: `¿Estás seguro de eliminar la unidad "${nombre}"? Esta acción no se puede deshacer.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear formulario dinámico para enviar DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/unidades-medida/${id}`;
            form.style.display = 'none';
            
            const csrfInput = document.createElement('input');
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endsection