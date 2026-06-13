{{-- resources/views/formas_pago/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Formas de Pago')
@section('page-title', 'Formas de Pago - Catálogo Global')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Formas de Pago</span></li>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Botones de acción --}}
    <div class="flex justify-between gap-4">
        <a href="{{ route('formas_pago.configurar.empresa', session('empresa_activa_id')) }}" 
           class="flex items-center gap-2 px-4 py-2 text-white transition rounded-lg bg-cyan-600 hover:bg-cyan-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Configurar por empresa
        </a>
        <a href="{{ route('formas_pago.create') }}" 
           class="flex items-center gap-2 px-4 py-2 text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Forma de Pago
        </a>
    </div>

    {{-- Tabla de catálogo global --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="px-4 py-3 border-b bg-gray-50">
            <p class="text-sm text-gray-500">Catálogo global de formas de pago</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Clave</th>
                        <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Icono</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Orden</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Requiere Ref.</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Requiere Auth.</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado Global</th>
                        <th class="px-4 py-3 text-xs font-medium text-center text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($formasPago as $forma)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm">{{ $forma->clave }}</td>
                        <td class="px-4 py-3 font-medium">{{ $forma->nombre }}</td>
                        <td class="px-4 py-3 text-2xl text-center">{{ $forma->icono ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">{{ $forma->orden }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($forma->requiere_referencia)
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">Sí</span>
                            @else
                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">No</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($forma->requiere_autorizacion)
                                <span class="px-2 py-1 text-xs text-orange-700 bg-orange-100 rounded-full">Sí</span>
                            @else
                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">No</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="toggleActivo({{ $forma->id }})" 
                                    class="px-2 py-1 text-xs rounded-full transition 
                                           {{ $forma->activo_global ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                {{ $forma->activo_global ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('formas_pago.edit', $forma) }}" 
                                   class="p-1 text-blue-600 transition hover:text-blue-800" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button onclick="confirmDelete({{ $forma->id }}, '{{ $forma->nombre }}')" 
                                        class="p-1 text-red-600 transition hover:text-red-800" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            No hay formas de pago registradas en el catálogo global.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $formasPago->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleActivo(id) {
    fetch(`/formas-pago/${id}/toggle-activo`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => Swal.fire('Error', 'No se pudo cambiar el estado', 'error'));
}

function confirmDelete(id, nombre) {
    Swal.fire({
        title: '¿Eliminar forma de pago?',
        html: `Se eliminará <strong>${nombre}</strong> del catálogo global.<br><span class="text-red-500">⚠️ Esta acción no se puede deshacer.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/formas-pago/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endsection