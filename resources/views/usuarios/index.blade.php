@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex flex-wrap items-center gap-2">
        @if(isset($empresaActiva))
            <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        @if(isset($sucursalActiva) && $sucursalActiva)
            <span class="px-3 py-1.5 bg-cyan-100 text-cyan-700 rounded-full text-sm font-medium">📍 {{ $sucursalActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $usuarios->count() }} de {{ $usuarios->total() }} usuarios</span>
    </div>

    @can('exportar_usuarios')
    <a href="{{ route('usuarios.export') }}"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
        📥 Exportar Excel
    </a>
    @endcan
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex flex-col items-start justify-between gap-4 p-6 border-b sm:flex-row sm:items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de usuarios</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los usuarios de la empresa activa</p>
        </div>
        
        @can('crear_usuarios')
        <a href="{{ route('usuarios.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo usuario
        </a>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="text-left bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Roles</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($usuarios as $usuario)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center text-sm font-bold text-white rounded-full shadow w-9 h-9 bg-gradient-to-br from-indigo-600 to-cyan-500">
                                {{ strtoupper(substr($usuario->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-slate-800">{{ $usuario->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $usuario->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $usuario->sucursal->nombre ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach($usuario->roles as $role)
                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm {{ $usuario->activo ? 'text-green-600' : 'text-red-600' }}">{{ $usuario->activo ? '● Activo' : '● Inactivo' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @can('ver_usuarios')
                            <a href="{{ route('usuarios.show', $usuario) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            @endcan
                            @can('editar_usuarios')
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            @endcan
                            @can('modificar_permisos_usuarios')
                            <a href="{{ route('usuarios.permisos.edit', $usuario) }}" class="p-2 text-gray-400 hover:text-purple-600" title="Permisos">🔐</a>
                            @endcan
                            @can('eliminar_usuarios')
                                @if($usuario->id !== auth()->id())
                                <button type="button" class="p-2 text-gray-400 btn-eliminar-usuario hover:text-red-600"
                                    data-id="{{ $usuario->id }}" data-nombre="{{ $usuario->name }}" title="Eliminar">🗑️</button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay usuarios</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">{{ $usuarios->links() }}</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof axios === 'undefined') {
        console.warn('Axios no disponible');
        return;
    }
    
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    const canDelete = @json(auth()->user()->can('eliminar_usuarios'));
    
    document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!canDelete) { 
                Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); 
                return; 
            }
            
            const { id, nombre } = btn.dataset;
            const { isConfirmed } = await Swal.fire({
                title: '¿Eliminar usuario?',
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
                const res = await axios.delete(`/usuarios/${id}`);
                if (res.data?.success !== false) { 
                    await Swal.fire({ icon: 'success', title: 'Eliminado', timer: 2000 }); 
                    location.reload(); 
                }
            } catch(e) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: e.response?.data?.message || 'Error', 
                    confirmButtonColor: '#ef4444' 
                });
            }
        });
    });
});
</script>
@endsection