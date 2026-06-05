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

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

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
        <a href="{{ route('proveedores.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo proveedor
        </a>
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
                <tr class="transition hover:bg-gray-50">
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
                        <span class="px-2 py-1 text-xs rounded-full {{ $proveedor->productos->count() > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $proveedor->productos->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($proveedor->activo)
                            <span class="text-sm text-green-600">● Activo</span>
                        @else
                            <span class="text-sm text-red-600">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('proveedores.show', $proveedor) }}" class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">
                                👁️
                            </a>
                            <a href="{{ route('proveedores.edit', $proveedor) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">
                                ✏️
                            </a>
                            <button type="button" onclick="toggleActivo({{ $proveedor->id }}, {{ $proveedor->activo ? 'true' : 'false' }}, '{{ $proveedor->nombre }}')" 
                                    class="p-2 text-gray-400 transition hover:text-indigo-600" title="{{ $proveedor->activo ? 'Desactivar' : 'Activar' }}">
                                {{ $proveedor->activo ? '🔴' : '🟢' }}
                            </button>
                            <button type="button" onclick="eliminarProveedor({{ $proveedor->id }}, '{{ $proveedor->nombre }}', {{ $proveedor->productos->count() }})" 
                                    class="p-2 text-gray-400 transition hover:text-red-600" title="Eliminar">
                                🗑️
                            </button>
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

<script>
function toggleActivo(id, activo, nombre) {
    const accion = activo ? 'desactivar' : 'activar';
    
    Swal.fire({
        title: `¿${accion === 'activar' ? 'Activar' : 'Desactivar'} proveedor?`,
        text: `¿Estás seguro de ${accion} el proveedor "${nombre}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/proveedores/${id}/toggle-activo`, {
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

function eliminarProveedor(id, nombre, productosCount) {
    if (productosCount > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No se puede eliminar',
            text: `El proveedor "${nombre}" tiene ${productosCount} producto(s) asociados. No se puede eliminar.`,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    Swal.fire({
        title: '¿Eliminar proveedor?',
        text: `¿Estás seguro de eliminar el proveedor "${nombre}"? Esta acción no se puede deshacer.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/proveedores/${id}`;
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