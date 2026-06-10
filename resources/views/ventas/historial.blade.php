@extends('layouts.app')

@section('title', 'Historial de Ventas')
@section('page-title', 'Historial de Ventas')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('ventas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Ventas
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Historial</span>
    </li>
@endsection

@section('content')
<div class="space-y-5">
    {{-- Buscador y filtros --}}
    <div class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="relative flex-1">
                <svg class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 left-3 top-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Buscar por folio, cliente o usuario..." 
                    class="w-full py-2 pr-4 border rounded-lg pl-9 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex flex-wrap gap-2">
                <select id="tipoFilter" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos los tipos</option>
                    <option value="contado">💵 Contado</option>
                    <option value="credito">📋 Crédito</option>
                </select>
                <select id="estadoFilter" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    <option value="completada">✅ Completada</option>
                    <option value="cancelada">❌ Cancelada</option>
                    <option value="pendiente">⏳ Pendiente</option>
                </select>
                <input type="date" id="fechaFilter" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <button id="limpiarFiltros" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
                    🗑️ Limpiar
                </button>
                {{-- Exportar Excel: Solo Super Admin y Administrador --}}
                @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
                <a href="{{ route('reportes.ventas.exportar') }}" 
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-lg hover:bg-green-700">
                    📥 Exportar Excel
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabla de ventas --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Folio</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody id="ventasTableBody" class="divide-y divide-gray-100">
                    @forelse($ventas as $venta)
                    <tr class="transition venta-row hover:bg-gray-50" 
                        id="venta-row-{{ $venta->id }}"
                        data-id="{{ $venta->id }}"
                        data-folio="{{ $venta->folio }}"
                        data-cliente="{{ strtolower($venta->cliente->nombre ?? 'mostrador') }}"
                        data-usuario="{{ strtolower($venta->usuario->name) }}"
                        data-tipo="{{ $venta->tipo }}"
                        data-estado="{{ $venta->estado }}"
                        data-fecha="{{ $venta->fecha_venta->format('Y-m-d') }}">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-medium text-indigo-600">{{ $venta->folio }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            {{ $venta->cliente->nombre ?? 'Cliente mostrador' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            {{ $venta->fecha_venta->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-right text-indigo-600">
                            ${{ number_format($venta->total, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($venta->tipo == 'contado')
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">💵 Contado</span>
                            @else
                                <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">📋 Crédito</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @switch($venta->estado)
                                @case('completada')
                                    <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">✅ Completada</span>
                                    @break
                                @case('cancelada')
                                    <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">❌ Cancelada</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 text-xs text-yellow-700 bg-yellow-100 rounded-full">⏳ Pendiente</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            {{ $venta->usuario->name }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Ver detalle: Todos los roles --}}
                                <a href="{{ route('ventas.show', $venta) }}" class="p-1.5 text-gray-400 hover:text-indigo-600" title="Ver detalle">👁️</a>
                                
                                {{-- Ticket: Todos los roles --}}
                                <a href="{{ route('ventas.ticket', $venta) }}" target="_blank" class="p-1.5 text-gray-400 hover:text-indigo-600" title="Ticket">🧾</a>
                                
                                {{-- Pagarés: Solo para crédito y con permiso --}}
                                @if($venta->tipo == 'credito' && $venta->credito && auth()->user()->hasRole(['Super Admin', 'Administrador', 'Cobrador']))
                                <a href="{{ route('ventas.pagares', $venta->credito->id) }}" target="_blank" class="p-1.5 text-gray-400 hover:text-green-600" title="Pagarés">📄</a>
                                @endif
                                
                                {{-- Cancelar venta: Solo Super Admin y Administrador --}}
                                @if(auth()->user()->hasRole(['Super Admin', 'Administrador']) && $venta->estado !== 'cancelada')
                                <button type="button" 
                                    class="btn-cancelar p-1.5 text-gray-400 hover:text-red-600" 
                                    data-id="{{ $venta->id }}"
                                    data-folio="{{ $venta->folio }}"
                                    title="Cancelar venta">
                                    ❌
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            No hay ventas registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($ventas->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $ventas->links() }}
        </div>
        @endif
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
        
        // ==================== FILTROS ====================
        function filtrarVentas() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const tipoFilter = document.getElementById('tipoFilter').value;
            const estadoFilter = document.getElementById('estadoFilter').value;
            const fechaFilter = document.getElementById('fechaFilter').value;

            document.querySelectorAll('.venta-row').forEach(row => {
                const folio = row.dataset.folio.toLowerCase();
                const cliente = row.dataset.cliente;
                const usuario = row.dataset.usuario;
                const tipo = row.dataset.tipo;
                const estado = row.dataset.estado;
                const fecha = row.dataset.fecha;

                const matchSearch = !searchTerm || folio.includes(searchTerm) || cliente.includes(searchTerm) || usuario.includes(searchTerm);
                const matchTipo = !tipoFilter || tipo === tipoFilter;
                const matchEstado = !estadoFilter || estado === estadoFilter;
                const matchFecha = !fechaFilter || fecha === fechaFilter;

                row.style.display = (matchSearch && matchTipo && matchEstado && matchFecha) ? '' : 'none';
            });
        }

        document.getElementById('searchInput')?.addEventListener('input', filtrarVentas);
        document.getElementById('tipoFilter')?.addEventListener('change', filtrarVentas);
        document.getElementById('estadoFilter')?.addEventListener('change', filtrarVentas);
        document.getElementById('fechaFilter')?.addEventListener('change', filtrarVentas);
        document.getElementById('limpiarFiltros')?.addEventListener('click', () => {
            document.getElementById('searchInput').value = '';
            document.getElementById('tipoFilter').value = '';
            document.getElementById('estadoFilter').value = '';
            document.getElementById('fechaFilter').value = '';
            filtrarVentas();
        });
        
        // ==================== CANCELAR VENTA ====================
        const cancelarBtns = document.querySelectorAll('.btn-cancelar');
        
        cancelarBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const { id, folio } = btn.dataset;
                
                const confirm = await Swal.fire({
                    title: '¿Cancelar venta?',
                    html: `Venta: <strong>${folio}</strong><br><br>Esta acción no se puede deshacer. El stock será restaurado.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Cancelar'
                });
                
                if (confirm.isConfirmed) {
                    Swal.fire({
                        title: 'Cancelando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    try {
                        const response = await axios.post(`/ventas/${id}/cancelar`);
                        const data = response.data;
                        
                        Swal.fire({
                            icon: data.icon || 'success',
                            title: data.success ? 'Venta cancelada' : 'Error',
                            text: data.message,
                            confirmButtonText: 'Cerrar'
                        }).then(() => {
                            if (data.success) {
                                location.reload();
                            }
                        });
                    } catch (error) {
                        const msg = error.response?.data?.message || 'Error al cancelar la venta';
                        showSwal('error', 'Error', msg);
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection