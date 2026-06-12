@extends('layouts.app')

@section('title', 'Unidades de Medida')
@section('page-title', 'Unidades de Medida')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Unidades de Medida</span></li>
@endsection

@section('content')

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-400">Mostrando {{ $unidades->count() }} de {{ $unidades->total() }}
                unidades</span>
        </div>

        @can('crear_unidades_medida')
            <a href="{{ route('unidades-medida.create') }}"
                class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                + Nueva unidad
            </a>
        @endcan
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
                        <tr id="unidad-row-{{ $unidad->id }}" class="transition hover:bg-gray-50">
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
                            <td class="px-6 py-4"><span class="text-sm text-gray-500">{{ $unidad->tipo }}</span></td>
                            <td class="px-6 py-4"><span
                                    class="px-2 py-1 font-mono text-xs bg-gray-100 rounded">{{ $unidad->simbolo ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-500">
                                <span
                                    class="px-2 py-1 rounded-full {{ $unidad->insumos->count() > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $unidad->insumos->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm {{ $unidad->activo ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $unidad->activo ? '● Activo' : '● Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @can('editar_unidades_medida')
                                        <a href="{{ route('unidades-medida.edit', $unidad) }}"
                                            class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">✏️</a>
                                    @endcan

                                    @can('eliminar_unidades_medida')
                                        @if($unidad->activo)
                                            @if($unidad->insumos->count() > 0)
                                                <span class="p-2 text-gray-300 cursor-not-allowed"
                                                    title="Tiene {{ $unidad->insumos->count() }} insumo(s)">🔒</span>
                                            @else
                                                <button type="button" class="p-2 text-gray-400 transition btn-desactivar hover:text-red-600"
                                                    data-id="{{ $unidad->id }}" data-nombre="{{ $unidad->nombre }}"
                                                    title="Desactivar">🗑️</button>
                                            @endif
                                        @endif
                                    @endcan

                                    @can('editar_unidades_medida')
                                        @if(!$unidad->activo)
                                            <button type="button"
                                                class="p-2 text-gray-400 transition btn-reactivar hover:text-green-600"
                                                data-id="{{ $unidad->id }}" data-nombre="{{ $unidad->nombre }}"
                                                title="Reactivar">✅</button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">No hay unidades registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">{{ $unidades->links() }}</div>
    </div>

    {{-- Script directo --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof axios === 'undefined') { console.error('Axios no disponible'); return; }

            axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
            axios.defaults.headers.common['Accept'] = 'application/json';

            const canDelete = @json(auth()->user()->can('eliminar_unidades_medida'));
            const canEdit = @json(auth()->user()->can('editar_unidades_medida'));

            // DESACTIVAR
            // DESACTIVAR - Cambiar axios.delete por axios.put
            document.querySelectorAll('.btn-desactivar').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!canDelete) {
                        Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' });
                        return;
                    }

                    const { id, nombre } = btn.dataset;

                    const { isConfirmed } = await Swal.fire({
                        title: '¿Desactivar?',
                        html: `Unidad: <strong>${nombre}</strong>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, desactivar',
                        cancelButtonText: 'Cancelar'
                    });

                    if (!isConfirmed) return;

                    Swal.fire({
                        title: 'Desactivando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        // ✅ Cambiar a PUT y usar ruta 'desactivar'
                        const res = await axios.put(`/unidades-medida/${id}/desactivar`);

                        if (res.data?.success) {
                            await Swal.fire({ icon: 'success', title: '¡Desactivada!', timer: 2000 });
                            location.reload();
                        } else {
                            throw new Error(res.data?.message || 'Error');
                        }
                    } catch (e) {
                        const msg = e.response?.data?.message || 'Error al desactivar';
                        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
                    }
                });
            });

            // REACTIVAR
            document.querySelectorAll('.btn-reactivar').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!canEdit) { Swal.fire({ icon: 'error', title: 'Acceso denegado', confirmButtonColor: '#ef4444' }); return; }
                    const { id, nombre } = btn.dataset;
                    const { isConfirmed } = await Swal.fire({
                        title: '¿Reactivar?', html: `Unidad: <strong>${nombre}</strong>`, icon: 'question',
                        showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, reactivar', cancelButtonText: 'Cancelar'
                    });
                    if (!isConfirmed) return;
                    Swal.fire({ title: 'Reactivando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    try {
                        const res = await axios.put(`/unidades-medida/${id}/reactivar`);
                        if (res.data?.success !== false) { await Swal.fire({ icon: 'success', title: '¡Reactivada!', timer: 2000 }); location.reload(); }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
                    }
                });
            });
        });
    </script>
@endsection