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
    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('clientes.export') }}" 
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 shadow rounded-xl hover:bg-green-700">
        📥 Exportar Excel
    </a>
    @endif
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="flex items-center justify-between p-6 border-b">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Lista de clientes</h2>
            <p class="mt-1 text-sm text-gray-500">Administra los clientes de la empresa</p>
        </div>
        <a href="{{ route('clientes.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo cliente
        </a>
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
                            <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full shadow bg-gradient-to-br from-blue-500 to-cyan-500">👤</div>
                            <span class="font-medium text-slate-800">{{ $cliente->nombre }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $cliente->tipo == 'credito' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
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
                        @if($cliente->activo)
                            <span class="text-sm text-green-600">● Activo</span>
                        @else
                            <span class="text-sm text-red-600">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('clientes.show', $cliente) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('clientes.edit', $cliente) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            
                            {{-- Solo Super Admin y Administrador pueden ver el botón eliminar --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline delete-form" data-cliente-id="{{ $cliente->id }}" data-cliente-nombre="{{ $cliente->nombre }}" data-cliente-tipo="{{ $cliente->tipo }}">
                                @csrf 
                                @method('DELETE')
                                <button type="button" class="p-2 text-gray-400 delete-btn hover:text-red-600" title="Eliminar">🗑️</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No hay clientes registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $clientes->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Seleccionar todos los botones de eliminar
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const form = this.closest('.delete-form');
                const clienteId = form.dataset.clienteId;
                const clienteNombre = form.dataset.clienteNombre;
                const clienteTipo = form.dataset.clienteTipo;
                
                // Verificar si es cliente de crédito
                if (clienteTipo === 'credito') {
                    Swal.fire({
                        title: '⚠️ No se puede eliminar',
                        text: 'Este cliente tiene crédito activo. No se puede eliminar clientes con crédito asignado.',
                        icon: 'warning',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                
                // Confirmar eliminación con SweetAlert2
                Swal.fire({
                    title: '¿Eliminar cliente?',
                    html: `Estás a punto de eliminar al cliente <strong>${clienteNombre}</strong><br>Esta acción no se puede deshacer.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loading
                        Swal.fire({
                            title: 'Eliminando...',
                            text: 'Por favor espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Enviar el formulario
                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                _method: 'DELETE'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: '¡Eliminado!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonText: 'Cerrar'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error al eliminar el cliente',
                                icon: 'error',
                                confirmButtonText: 'Cerrar'
                            });
                        });
                    }
                });
            });
        });
    });
</script>
@endpush

@endsection