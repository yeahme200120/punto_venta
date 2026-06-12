@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles')

@section('content')

<x-alert type="error" :message="session('error')" />
<x-alert type="success" :message="session('success')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex flex-wrap items-center gap-2">
        @if(isset($empresaActiva))
            <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $roles->count() }} de {{ $roles->total() }} roles</span>
    </div>

    @can('ver_roles')
    <a href="{{ route('roles.export') }}"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
        📥 Exportar Excel
    </a>
    @endcan
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de roles</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los roles y permisos del sistema</p>
        </div>
        
        @can('crear_roles')
        <a href="{{ route('roles.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo rol
        </a>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Permisos</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Usuarios</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($roles as $role)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full shadow bg-gradient-to-br from-purple-600 to-pink-500">
                                {{ strtoupper(substr($role->name, 0, 1)) }}
                            </div>
                            <span class="font-semibold text-slate-800">{{ $role->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">{{ $role->permissions->count() }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">{{ $role->users_count ?? $role->users->count() }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @can('ver_roles')
                            <a href="{{ route('roles.show', $role) }}" class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver">👁️</a>
                            @endcan
                            
                            @can('editar_roles')
                            <a href="{{ route('roles.edit', $role) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">✏️</a>
                            <a href="{{ route('roles.permisos_rol.edit', $role) }}" class="p-2 text-gray-400 transition hover:text-purple-600" title="Permisos">🔑</a>
                            @endcan
                            
                            @can('eliminar_roles')
                                @if($role->name !== 'Super Admin')
                                <button type="button" class="btn-eliminar-rol p-2 text-gray-400 transition hover:text-red-600"
                                    data-id="{{ $role->id }}" data-nombre="{{ $role->name }}" title="Eliminar">🗑️</button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">No hay roles creados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">{{ $roles->links() }}</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof axios === 'undefined') return;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    const canDelete = @json(auth()->user()->can('eliminar_roles'));
    
    document.querySelectorAll('.btn-eliminar-rol').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!canDelete) { Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return; }
            
            const { id, nombre } = btn.dataset;
            const { isConfirmed } = await Swal.fire({
                title: '¿Eliminar rol?',
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
                const res = await axios.delete(`/roles/${id}`);
                if (res.data?.success !== false) { await Swal.fire({ icon: 'success', title: 'Eliminado', timer: 2000 }); location.reload(); }
            } catch(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
            }
        });
    });
});
</script>
@endsection