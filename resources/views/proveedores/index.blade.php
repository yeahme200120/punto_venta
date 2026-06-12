@extends('layouts.app')

@section('title', 'Proveedores')
@section('page-title', 'Proveedores')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Proveedores</span></li>
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
        
        @can('ver_proveedores')
        <a href="{{ route('proveedores.export') }}" 
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
            📥 Exportar Excel
        </a>
        @endcan
    </div>
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de proveedores</h2>
            <p class="mt-1 text-sm text-gray-500">Administra los proveedores de la empresa</p>
        </div>
        
        @can('crear_proveedores')
        <a href="{{ route('proveedores.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo proveedor
        </a>
        @endcan
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
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Insumos</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($proveedores as $proveedor)
                @php 
                    $prodCount = $proveedor->productos->count();
                    $insumoCount = $proveedor->insumos->count();
                    $totalAsociados = $prodCount + $insumoCount;
                @endphp
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-full shadow bg-gradient-to-br from-amber-500 to-orange-500">
                                {{ strtoupper(substr($proveedor->nombre, 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-800">{{ $proveedor->nombre }}</span>
                                @if($proveedor->direccion)
                                <p class="text-xs text-gray-400">{{ Str::limit($proveedor->direccion, 40) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4"><span class="font-mono text-sm">{{ $proveedor->rfc ?? '—' }}</span></td>
                    <td class="px-6 py-4 text-sm">{{ $proveedor->telefono ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $proveedor->correo ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs rounded-full {{ $prodCount > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $prodCount }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs rounded-full {{ $insumoCount > 0 ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $insumoCount }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm {{ $proveedor->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $proveedor->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @can('ver_proveedores')
                            <a href="{{ route('proveedores.show', $proveedor) }}" class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">👁️</a>
                            @endcan
                            
                            @can('editar_proveedores')
                            <a href="{{ route('proveedores.edit', $proveedor) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">✏️</a>
                            @endcan
                            
                            {{-- Toggle --}}
                            @can('editar_proveedores')
                                @if($proveedor->activo)
                                    @if($totalAsociados == 0)
                                    <button type="button" class="p-2 text-gray-400 transition btn-desactivar hover:text-red-600"
                                        data-id="{{ $proveedor->id }}" data-nombre="{{ $proveedor->nombre }}" title="Desactivar">🔴</button>
                                    @else
                                    <span class="p-2 text-gray-300 cursor-not-allowed" 
                                          title="Tiene {{ $prodCount }} producto(s) y {{ $insumoCount }} insumo(s). No se puede desactivar.">🔒</span>
                                    @endif
                                @else
                                <button type="button" class="p-2 text-gray-400 transition btn-reactivar hover:text-green-600"
                                    data-id="{{ $proveedor->id }}" data-nombre="{{ $proveedor->nombre }}" title="Reactivar">🟢</button>
                                @endif
                            @endcan
                            
                            {{-- Eliminar --}}
                            @can('eliminar_proveedores')
                                @if($totalAsociados == 0)
                                <button type="button" class="p-2 text-gray-400 transition btn-eliminar hover:text-red-600"
                                    data-id="{{ $proveedor->id }}" data-nombre="{{ $proveedor->nombre }}" title="Eliminar">🗑️</button>
                                @else
                                <span class="p-2 text-gray-300 cursor-not-allowed" 
                                      title="Tiene {{ $prodCount }} producto(s) y {{ $insumoCount }} insumo(s). No se puede eliminar.">🔒</span>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">
                    No hay proveedores registrados
                    @can('crear_proveedores')
                    <div class="mt-2"><a href="{{ route('proveedores.create') }}" class="text-indigo-600 hover:text-indigo-800">+ Crear primer proveedor</a></div>
                    @endcan
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t">{{ $proveedores->links() }}</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof axios === 'undefined') { console.error('Axios no disponible'); return; }
    
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    const canEdit = @json(auth()->user()->can('editar_proveedores'));
    const canDelete = @json(auth()->user()->can('eliminar_proveedores'));

    // DESACTIVAR
    document.querySelectorAll('.btn-desactivar').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!canEdit) { Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return; }
            const { id, nombre } = btn.dataset;
            const { isConfirmed } = await Swal.fire({
                title: '¿Desactivar?', html: `<strong>${nombre}</strong>`, icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar'
            });
            if (!isConfirmed) return;
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await axios.put(`/proveedores/${id}/desactivar`);
                if (res.data?.success) { await Swal.fire({ icon: 'success', title: '¡Desactivado!', timer: 2000 }); location.reload(); }
                else throw new Error(res.data?.message || 'Error');
            } catch(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
        });
    });

    // REACTIVAR
    document.querySelectorAll('.btn-reactivar').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!canEdit) { Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return; }
            const { id, nombre } = btn.dataset;
            const { isConfirmed } = await Swal.fire({
                title: '¿Reactivar?', html: `<strong>${nombre}</strong>`, icon: 'question',
                showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, reactivar', cancelButtonText: 'Cancelar'
            });
            if (!isConfirmed) return;
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await axios.put(`/proveedores/${id}/reactivar`);
                if (res.data?.success) { await Swal.fire({ icon: 'success', title: '¡Reactivado!', timer: 2000 }); location.reload(); }
            } catch(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
        });
    });

    // ELIMINAR
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!canDelete) { Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return; }
            const { id, nombre } = btn.dataset;
            const { isConfirmed } = await Swal.fire({
                title: '¿Eliminar permanentemente?', 
                html: `<strong>${nombre}</strong><br><br>⚠️ Esta acción NO se puede deshacer.`, 
                icon: 'error',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            });
            if (!isConfirmed) return;
            Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await axios.delete(`/proveedores/${id}`);
                if (res.data?.success) { await Swal.fire({ icon: 'success', title: '¡Eliminado!', timer: 2000 }); location.reload(); }
            } catch(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
        });
    });
});
</script>
@endsection