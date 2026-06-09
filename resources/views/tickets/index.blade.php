@extends('layouts.app')

@section('title', 'Configuración de Tickets')
@section('page-title', 'Configuración de Tickets')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Tickets</span></li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex justify-end gap-2">
        <a href="{{ route('ticket.create') }}" class="flex items-center gap-2 px-4 py-2 text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nueva Configuración
        </a>
        <a href="{{ route('ticket.diseno') }}" class="flex items-center gap-2 px-4 py-2 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>
            Diseño de Tickets
        </a>
    </div>

    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Empresa</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Logo</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Ancho papel</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Auto imprimir</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Copias</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($configuraciones as $config)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">{{ ucfirst($config->tipo) }}</span></td>
                        <td class="px-4 py-3 font-medium">{{ $config->nombre_empresa ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($config->logo_url && $config->mostrar_logo)
                                <img src="{{ $config->logo_url }}" class="w-auto h-8 mx-auto">
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">{{ $config->ancho_papel }}</td>
                        <td class="px-4 py-3 text-center">{{ $config->auto_imprimir ? '✅ Sí' : '❌ No' }}</td>
                        <td class="px-4 py-3 text-center">{{ $config->copias }}</td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="toggleActivo({{ $config->id }})" class="px-2 py-1 text-xs rounded-full transition toggle-btn-{{ $config->id }} {{ $config->activo ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                {{ $config->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('ticket.show', $config) }}" class="p-1 text-gray-600 hover:text-indigo-600" title="Ver"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></a>
                                <a href="{{ route('ticket.edit', $config) }}" class="p-1 text-blue-600 hover:text-blue-800" title="Editar"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a>
                                <button onclick="confirmDelete({{ $config->id }}, '{{ $config->tipo }}')" class="p-1 text-red-600 hover:text-red-800" title="Eliminar"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">No hay configuraciones de ticket registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $configuraciones->links() }}</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleActivo(id) {
    fetch(`/ticket/${id}/toggle-activo`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
    .then(res => res.json()).then(data => { if(data.success) Swal.fire({ icon: data.icon, title: data.title, text: data.message, timer: 1500, showConfirmButton: false }).then(() => location.reload()); else Swal.fire({ icon: 'error', title: 'Error', text: data.message }); })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cambiar el estado' }));
}
function confirmDelete(id, tipo) {
    Swal.fire({ title: '¿Eliminar configuración?', html: `Se eliminará la configuración para <strong>${tipo}</strong>.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' })
    .then((result) => { if(result.isConfirmed) { fetch(`/ticket/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' } }).then(res => res.json()).then(data => { if(data.success) Swal.fire({ icon: data.icon, title: data.title, text: data.message, timer: 1500, showConfirmButton: false }).then(() => location.reload()); else Swal.fire({ icon: 'error', title: 'Error', text: data.message }); }).catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al eliminar' })); } });
}
</script>
@endsection