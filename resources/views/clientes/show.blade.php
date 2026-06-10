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
    
    {{-- Exportar Excel: Solo Super Admin y Administrador --}}
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
        
        {{-- Crear cliente: Super Admin, Administrador, Vendedor --}}
        @if(auth()->user()->hasRole(['Super Admin', 'Administrador', 'Vendedor']))
        <a href="{{ route('clientes.create') }}"
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nuevo cliente
        </a>
        @endif
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
                <tr class="transition hover:bg-gray-50" id="cliente-row-{{ $cliente->id }}">
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
                        <span class="estado-badge-{{ $cliente->id }} text-sm {{ $cliente->activo ? 'text-green-600' : 'text-red-600' }}">
                            {{ $cliente->activo ? '● Activo' : '● Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Ver detalle: Todos los roles --}}
                            <a href="{{ route('clientes.show', $cliente) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            
                            {{-- Editar: Super Admin, Administrador, Vendedor --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador', 'Vendedor']))
                            <a href="{{ route('clientes.edit', $cliente) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            @endif
                            
                            {{-- Desactivar/Reactivar: Solo Super Admin y Administrador --}}
                            @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                                @if($cliente->activo)
                                <button type="button" 
                                        class="p-2 text-gray-400 btn-desactivar hover:text-red-600" 
                                        data-id="{{ $cliente->id }}"
                                        data-nombre="{{ $cliente->nombre }}"
                                        data-tipo="{{ $cliente->tipo }}"
                                        title="Desactivar">
                                    🗑️
                                </button>
                                @else
                                <button type="button" 
                                        class="p-2 text-gray-400 btn-reactivar hover:text-green-600" 
                                        data-id="{{ $cliente->id }}"
                                        data-nombre="{{ $cliente->nombre }}"
                                        title="Reactivar">
                                    ✅
                                </button>
                                @endif
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
        
        // Configurar CSRF token para Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.headers.common['Content-Type'] = 'application/json';
        
        // ==================== DESACTIVAR CLIENTE ====================
        const desactivarBtns = document.querySelectorAll('.btn-desactivar');
        
        desactivarBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const clienteId = this.dataset.id;
                const clienteNombre = this.dataset.nombre;
                const clienteTipo = this.dataset.tipo;
                
                Swal.fire({
                    title: '¿Desactivar cliente?',
                    html: `Estás a punto de desactivar al cliente <strong>${clienteNombre}</strong><br><br>El cliente quedará inactivo pero sus datos se conservarán.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Desactivando...',
                            text: 'Por favor espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        axios.delete(`/clientes/${clienteId}`)
                            .then(response => {
                                const data = response.data;
                                
                                Swal.fire({
                                    icon: data.icon || 'success',
                                    title: 'Desactivado',
                                    text: data.message,
                                    confirmButtonText: 'Cerrar'
                                });
                                
                                if (data.success) {
                                    // Actualizar el estado visual sin recargar
                                    const estadoSpan = document.querySelector(`.estado-badge-${clienteId}`);
                                    if (estadoSpan) {
                                        estadoSpan.innerHTML = '● Inactivo';
                                        estadoSpan.classList.remove('text-green-600');
                                        estadoSpan.classList.add('text-red-600');
                                    }
                                    
                                    // Cambiar el botón de desactivar a reactivar
                                    const accionesDiv = btn.closest('.flex');
                                    if (accionesDiv) {
                                        const desactivarBtn = accionesDiv.querySelector('.btn-desactivar');
                                        if (desactivarBtn) {
                                            const newBtn = document.createElement('button');
                                            newBtn.type = 'button';
                                            newBtn.className = 'btn-reactivar p-2 text-gray-400 hover:text-green-600';
                                            newBtn.setAttribute('data-id', clienteId);
                                            newBtn.setAttribute('data-nombre', clienteNombre);
                                            newBtn.setAttribute('title', 'Reactivar');
                                            newBtn.innerHTML = '✅';
                                            
                                            // Agregar evento al nuevo botón
                                            newBtn.addEventListener('click', function() {
                                                window.reactivarCliente(this);
                                            });
                                            
                                            desactivarBtn.replaceWith(newBtn);
                                        }
                                    }
                                    
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                }
                            })
                            .catch(error => {
                                let mensajeError = 'Ocurrió un error al desactivar el cliente';
                                if (error.response && error.response.data && error.response.data.message) {
                                    mensajeError = error.response.data.message;
                                }
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: mensajeError,
                                    confirmButtonText: 'Cerrar'
                                });
                            });
                    }
                });
            });
        });
        
        // ==================== REACTIVAR CLIENTE ====================
        const reactivarBtns = document.querySelectorAll('.btn-reactivar');
        
        reactivarBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                window.reactivarCliente(this);
            });
        });
        
        // Función global para reactivar
        window.reactivarCliente = function(btn) {
            const clienteId = btn.dataset.id;
            const clienteNombre = btn.dataset.nombre;
            
            Swal.fire({
                title: '¿Reactivar cliente?',
                html: `Estás a punto de reactivar al cliente <strong>${clienteNombre}</strong><br><br>El cliente volverá a estar activo en el sistema.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Reactivando...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    axios.put(`/clientes/${clienteId}/reactivar`)
                        .then(response => {
                            const data = response.data;
                            
                            Swal.fire({
                                icon: data.icon || 'success',
                                title: 'Reactivado',
                                text: data.message,
                                confirmButtonText: 'Cerrar'
                            });
                            
                            if (data.success) {
                                // Actualizar el estado visual sin recargar
                                const estadoSpan = document.querySelector(`.estado-badge-${clienteId}`);
                                if (estadoSpan) {
                                    estadoSpan.innerHTML = '● Activo';
                                    estadoSpan.classList.remove('text-red-600');
                                    estadoSpan.classList.add('text-green-600');
                                }
                                
                                // Cambiar el botón de reactivar a desactivar
                                const accionesDiv = btn.closest('.flex');
                                if (accionesDiv) {
                                    const reactivarBtn = accionesDiv.querySelector('.btn-reactivar');
                                    if (reactivarBtn) {
                                        const newBtn = document.createElement('button');
                                        newBtn.type = 'button';
                                        newBtn.className = 'btn-desactivar p-2 text-gray-400 hover:text-red-600';
                                        newBtn.setAttribute('data-id', clienteId);
                                        newBtn.setAttribute('data-nombre', clienteNombre);
                                        newBtn.setAttribute('data-tipo', btn.dataset.tipo || 'contado');
                                        newBtn.setAttribute('title', 'Desactivar');
                                        newBtn.innerHTML = '🗑️';
                                        
                                        // Agregar evento al nuevo botón
                                        newBtn.addEventListener('click', function() {
                                            // Disparar evento de desactivar
                                            const event = new Event('click');
                                            this.dispatchEvent(event);
                                        });
                                        
                                        reactivarBtn.replaceWith(newBtn);
                                        
                                        // Reasignar evento desactivar
                                        document.querySelectorAll('.btn-desactivar').forEach(btnDes => {
                                            btnDes.removeEventListener('click', window.desactivarHandler);
                                            btnDes.addEventListener('click', function(e) {
                                                const id = this.dataset.id;
                                                const nombre = this.dataset.nombre;
                                                const tipo = this.dataset.tipo;
                                                
                                                Swal.fire({
                                                    title: '¿Desactivar cliente?',
                                                    html: `Estás a punto de desactivar al cliente <strong>${nombre}</strong>`,
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#d33',
                                                    confirmButtonText: 'Sí, desactivar'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        Swal.fire({ title: 'Desactivando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                                                        axios.delete(`/clientes/${id}`).then(res => {
                                                            Swal.fire({ icon: 'success', title: 'Desactivado', text: res.data.message });
                                                            setTimeout(() => location.reload(), 1500);
                                                        }).catch(err => {
                                                            Swal.fire({ icon: 'error', title: 'Error', text: err.response?.data?.message || 'Error' });
                                                        });
                                                    }
                                                });
                                            });
                                        });
                                    }
                                }
                                
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            }
                        })
                        .catch(error => {
                            let mensajeError = 'Ocurrió un error al reactivar el cliente';
                            if (error.response && error.response.data && error.response.data.message) {
                                mensajeError = error.response.data.message;
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: mensajeError,
                                confirmButtonText: 'Cerrar'
                            });
                        });
                }
            });
        };
        
        // Reasignar eventos desactivar
        document.querySelectorAll('.btn-desactivar').forEach(btn => {
            btn.removeEventListener('click', window.desactivarHandler);
            btn.addEventListener('click', function(e) {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;
                
                Swal.fire({
                    title: '¿Desactivar cliente?',
                    html: `Estás a punto de desactivar al cliente <strong>${nombre}</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, desactivar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Desactivando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                        axios.delete(`/clientes/${id}`).then(res => {
                            Swal.fire({ icon: 'success', title: 'Desactivado', text: res.data.message });
                            setTimeout(() => location.reload(), 1500);
                        }).catch(err => {
                            Swal.fire({ icon: 'error', title: 'Error', text: err.response?.data?.message || 'Error' });
                        });
                    }
                });
            });
        });
    });
</script>
@endpush

@endsection