@extends('layouts.app')

@section('title', 'Cotizaciones')
@section('page-title', 'Cotizaciones')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Cotizaciones</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex justify-end mb-4">
    <a href="{{ route('ventas.index') }}" 
       class="px-4 py-2 text-sm font-medium text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
        + Nueva Cotización
    </a>
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold text-slate-800">Listado de Cotizaciones</h2>
        <p class="mt-1 text-sm text-gray-500">Administra las cotizaciones generadas</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Folio</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Válida hasta</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($cotizaciones as $cotizacion)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm font-medium text-indigo-600">{{ $cotizacion->folio }}</span>
                    </td>
                    <td class="px-6 py-4">
                        {{ $cotizacion->cliente->nombre ?? 'Cliente mostrador' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-center">
                        {{ $cotizacion->fecha_cotizacion->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-center">
                        @if($cotizacion->fecha_validez)
                            <span class="{{ $cotizacion->fecha_validez->isPast() ? 'text-red-600' : 'text-green-600' }}">
                                {{ $cotizacion->fecha_validez->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-bold text-right text-indigo-600">
                        ${{ number_format($cotizacion->total, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @switch($cotizacion->estado)
                            @case('activa')
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">🟢 Activa</span>
                                @break
                            @case('convertida')
                                <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">🔄 Convertida</span>
                                @break
                            @case('vencida')
                                <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">⏰ Vencida</span>
                                @break
                            @case('cancelada')
                                <span class="px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded-full">❌ Cancelada</span>
                                @break
                            @default
                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">{{ $cotizacion->estado }}</span>
                        @endswitch
                     </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @if($cotizacion->estado == 'activa')
                            <button type="button" 
                                    onclick="cargarAlCarrito({{ $cotizacion->id }})"
                                    class="p-2 text-gray-400 transition hover:text-green-600"
                                    title="Cargar al carrito">
                                🛒
                            </button>
                            @endif
                            <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" 
                               target="_blank"
                               class="p-2 text-gray-400 transition hover:text-red-600"
                               title="Descargar PDF">
                                📄
                            </a>
                            <a href="{{ route('cotizaciones.show', $cotizacion) }}" 
                               class="p-2 text-gray-400 transition hover:text-indigo-600"
                               title="Ver detalle">
                                👁️
                            </a>
                            @if($cotizacion->estado == 'activa')
                            <button type="button" 
                                    onclick="cancelarCotizacion({{ $cotizacion->id }})"
                                    class="p-2 text-gray-400 transition hover:text-red-600"
                                    title="Cancelar">
                                ❌
                            </button>
                            @endif
                        </div>
                     </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No hay cotizaciones registradas
                        <div class="mt-2">
                            <a href="{{ route('ventas.index') }}" class="text-indigo-600 hover:text-indigo-800">
                                + Crear primera cotización
                            </a>
                        </div>
                     </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t">
        {{ $cotizaciones->links() }}
    </div>
</div>

<script>
function cargarAlCarrito(id) {
    Swal.fire({
        title: '¿Cargar cotización al carrito?',
        text: 'El carrito actual será reemplazado',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Sí, cargar'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(`/carrito/cargar-cotizacion/${id}`)
                .then(response => {
                    if (response.data.success) {
                        Swal.fire({
                            title: '¡Carrito cargado!',
                            text: `Se cargaron ${response.data.items.length} productos`,
                            icon: 'success',
                            confirmButtonText: 'Ir a vender'
                        }).then(() => {
                            window.location.href = '{{ route("ventas.index") }}';
                        });
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.response?.data?.message || 'Error al cargar', 'error');
                });
        }
    });
}

function cancelarCotizacion(id) {
    Swal.fire({
        title: '¿Cancelar cotización?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(`/cotizaciones/${id}`)
                .then(response => {
                    if (response.data.success) {
                        Swal.fire('Cancelada', 'Cotización cancelada correctamente', 'success');
                        location.reload();
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.response?.data?.message || 'Error al cancelar', 'error');
                });
        }
    });
}
</script>
@endsection