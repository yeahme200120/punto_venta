@extends('layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $clientes->count() }} de {{ $clientes->total() }} clientes</span>
    </div>
    
    @can('ver_clientes')
    <a href="{{ route('clientes.export') }}" 
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
        📥 Exportar Excel
    </a>
    @endcan
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de clientes</h2>
            <p class="mt-1 text-sm text-gray-500">Administra los clientes de la empresa</p>
        </div>
        
        @can('crear_clientes')
        <a href="{{ route('clientes.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo cliente
        </a>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Teléfono</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Correo</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Crédito</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clientes as $cliente)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full shadow bg-gradient-to-br from-blue-500 to-cyan-500">
                                {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                            </div>
                            <span class="font-medium text-slate-800">{{ $cliente->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $cliente->tipo == 'credito' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($cliente->tipo) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $cliente->telefono ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $cliente->correo ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-center">
                        @if($cliente->tipo == 'credito')
                            ${{ number_format($cliente->limite_credito, 2) }} / {{ $cliente->dias_credito }} días
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm {{ $cliente->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $cliente->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @can('ver_clientes')
                            <a href="{{ route('clientes.show', $cliente) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            @endcan
                            
                            @can('editar_clientes')
                            <a href="{{ route('clientes.edit', $cliente) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            @endcan
                            
                            @can('eliminar_clientes')
                                @if($cliente->tipo == 'credito')
                                    <span class="p-2 text-gray-300 cursor-not-allowed" title="No se puede eliminar, tiene crédito">🔒</span>
                                @else
                                    <button type="button" class="btn-eliminar p-2 text-gray-400 hover:text-red-600" 
                                        data-id="{{ $cliente->id }}" data-nombre="{{ $cliente->nombre }}" title="Eliminar">🗑️</button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No hay clientes registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">{{ $clientes->links() }}</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof axios === 'undefined') return;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    const canDelete = @json(auth()->user()->can('eliminar_clientes'));
    
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!canDelete) { Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return; }
            
            const { id, nombre } = btn.dataset;
            const { isConfirmed } = await Swal.fire({
                title: '¿Eliminar cliente?',
                html: `<strong>${nombre}</strong><br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            
            if (!isConfirmed) return;
            Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            
            try {
                const res = await axios.delete(`/clientes/${id}`);
                if (res.data?.success) { await Swal.fire({ icon: 'success', title: 'Eliminado', timer: 2000 }); location.reload(); }
                else throw new Error(res.data?.message || 'Error');
            } catch(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
        });
    });
});
</script>
@endsection