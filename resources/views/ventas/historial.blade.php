@extends('layouts.app')

@section('title', 'Historial de Ventas')
@section('page-title', 'Historial de Ventas')

@section('content')
    <div class="space-y-5">
        {{-- Info de caja activa o selector --}}
        @if(isset($cajasActivas) && $cajasActivas->count() > 1)
            <div class="p-4 mb-4 bg-white border border-indigo-200 shadow-sm rounded-2xl">
                <div class="flex items-center gap-3">
                    <span class="text-lg">🏦</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-700">Caja activa:</p>
                        <select id="cajaActivaSelect"
                            class="w-full mt-2 px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            @foreach($cajasActivas as $caja)
                                <option value="{{ $caja->id }}" {{ $cajaAbierta && $cajaAbierta->id == $caja->id ? 'selected' : '' }}>
                                    🏦 {{ $caja->caja->nombre }} | 👤 {{ $caja->usuario->name }} | 💰
                                    ${{ number_format($caja->monto_inicial, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @elseif(isset($cajaAbierta) && $cajaAbierta)
            <div class="p-3 mb-4 bg-green-50 border border-green-200 rounded-xl">
                <div class="flex items-center gap-2 text-sm text-green-700">
                    <span>🏦</span>
                    <span class="font-medium">{{ $cajaAbierta->caja->nombre }}</span>
                    <span class="text-green-400">|</span>
                    <span>👤 {{ $cajaAbierta->usuario->name }}</span>
                    <span class="text-green-400">|</span>
                    <span>💰
                        ${{ number_format($cajaAbierta->monto_inicial + $cajaAbierta->total_ingresos - $cajaAbierta->total_egresos, 2) }}</span>
                </div>
            </div>
        @else
            <div class="p-3 mb-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                <p class="text-sm text-yellow-700">⚠️ No hay caja abierta en esta sucursal</p>
            </div>
        @endif

        {{-- Buscador y filtros --}}
        <div class="p-4 bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="relative flex-1">
                    <input type="text" id="searchInput" placeholder="Buscar por folio, cliente o usuario..."
                        class="w-full py-2 pl-10 pr-4 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="tipoFilter" class="px-3 py-2 border rounded-lg">
                        <option value="">Todos los tipos</option>
                        <option value="contado">💵 Contado</option>
                        <option value="credito">📋 Crédito</option>
                    </select>
                    <select id="estadoFilter" class="px-3 py-2 border rounded-lg">
                        <option value="">Todos los estados</option>
                        <option value="completada">✅ Completada</option>
                        <option value="cancelada">❌ Cancelada</option>
                    </select>
                    <input type="date" id="fechaFilter" class="px-3 py-2 border rounded-lg">
                    <button id="limpiarFiltros" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">🗑️
                        Limpiar</button>

                    @can('ver_reportes')
                        <a href="{{ route('reportes.ventas.exportar') }}"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            📥 Exportar Excel
                        </a>
                    @endcan
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
                            <tr class="transition venta-row hover:bg-gray-50" data-folio="{{ strtolower($venta->folio) }}"
                                data-cliente="{{ strtolower($venta->cliente->nombre ?? 'mostrador') }}"
                                data-usuario="{{ strtolower($venta->usuario->name) }}" data-tipo="{{ $venta->tipo }}"
                                data-estado="{{ $venta->estado }}" data-fecha="{{ $venta->fecha_venta->format('Y-m-d') }}">
                                <td class="px-6 py-4"><span
                                        class="font-mono text-sm font-medium text-indigo-600">{{ $venta->folio }}</span></td>
                                <td class="px-6 py-4 text-sm">{{ $venta->cliente->nombre ?? 'Mostrador' }}</td>
                                <td class="px-6 py-4 text-sm text-center">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 font-bold text-right text-indigo-600">
                                    ${{ number_format($venta->total, 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $venta->tipo == 'contado' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $venta->tipo == 'contado' ? '💵 Contado' : '📋 Crédito' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $venta->estado == 'completada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $venta->estado == 'completada' ? '✅ Completada' : '❌ Cancelada' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">{{ $venta->usuario->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @can('ver_ventas')
                                            <a href="{{ route('ventas.show', $venta) }}"
                                                class="p-1.5 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                                            <a href="{{ route('ventas.ticket', $venta) }}" target="_blank"
                                                class="p-1.5 text-gray-400 hover:text-indigo-600" title="Ticket">🧾</a>
                                        @endcan

                                        @if($venta->tipo == 'credito' && $venta->credito)
                                            @can('ver_cobranza')
                                                <a href="{{ route('ventas.pagares', $venta->credito->id) }}" target="_blank"
                                                    class="p-1.5 text-gray-400 hover:text-green-600" title="Pagarés">📄</a>
                                            @endcan
                                        @endif

                                        @can('cancelar_ventas')
                                            @if($venta->estado !== 'cancelada')
                                                <button type="button" class="btn-cancelar p-1.5 text-gray-400 hover:text-red-600"
                                                    data-id="{{ $venta->id }}" data-folio="{{ $venta->folio }}"
                                                    title="Cancelar">❌</button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">No hay ventas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ventas->hasPages())
                <div class="px-6 py-4 border-t">{{ $ventas->links() }}</div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof axios === 'undefined') return;
            axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
            axios.defaults.headers.common['Accept'] = 'application/json';

            const canCancelar = @json(auth()->user()->can('cancelar_ventas'));

            // Filtros
            function filtrar() {
                const s = document.getElementById('searchInput').value.toLowerCase();
                const t = document.getElementById('tipoFilter').value;
                const e = document.getElementById('estadoFilter').value;
                const f = document.getElementById('fechaFilter').value;
                document.querySelectorAll('.venta-row').forEach(row => {
                    const match = (!s || row.dataset.folio.includes(s) || row.dataset.cliente.includes(s) || row.dataset.usuario.includes(s))
                        && (!t || row.dataset.tipo === t) && (!e || row.dataset.estado === e) && (!f || row.dataset.fecha === f);
                    row.style.display = match ? '' : 'none';
                });
            }
            ['searchInput', 'tipoFilter', 'estadoFilter', 'fechaFilter'].forEach(id => document.getElementById(id)?.addEventListener(id === 'searchInput' ? 'input' : 'change', filtrar));
            document.getElementById('limpiarFiltros')?.addEventListener('click', () => {
                ['searchInput', 'tipoFilter', 'estadoFilter', 'fechaFilter'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
                filtrar();
            });

            // Cancelar venta
            document.querySelectorAll('.btn-cancelar').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!canCancelar) { Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return; }
                    const { id, folio } = btn.dataset;
                    const { isConfirmed } = await Swal.fire({
                        title: '¿Cancelar venta?', html: `<strong>${folio}</strong><br>El stock será restaurado.`, icon: 'warning',
                        showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
                    });
                    if (!isConfirmed) return;
                    Swal.fire({ title: 'Cancelando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    try {
                        const res = await axios.post(`/ventas/${id}/cancelar`);
                        if (res.data?.success) { await Swal.fire({ icon: 'success', title: 'Cancelada', timer: 2000 }); location.reload(); }
                        else throw new Error(res.data?.message || 'Error');
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
                    }
                });
            });
        });
    </script>
@endsection