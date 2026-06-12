@extends('layouts.app')

@section('title', 'Cotizaciones')
@section('page-title', 'Cotizaciones')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Cotizaciones</span></li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />
{{-- ✅ INFO DE CAJA ABIERTA --}}
@if(isset($cajasActivas) && $cajasActivas->count() > 1)
    <div class="p-4 mb-4 bg-white border border-indigo-200 shadow-sm rounded-2xl">
        <div class="flex items-center gap-3">
            <span class="text-lg">🏦</span>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-700">Caja activa:</p>
                <select id="cajaActivaSelect" class="w-full mt-2 px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                        onchange="actualizarInfoCaja()">
                    @foreach($cajasActivas as $caja)
                        <option value="{{ $caja->id }}" 
                            data-nombre="{{ $caja->caja->nombre }}"
                            data-usuario="{{ $caja->usuario->name }}"
                            data-saldo="{{ $caja->monto_inicial + $caja->total_ingresos - $caja->total_egresos }}"
                            {{ isset($cajaAbierta) && $cajaAbierta->id == $caja->id ? 'selected' : '' }}>
                            🏦 {{ $caja->caja->nombre }} | 👤 {{ $caja->usuario->name }} | 💰 ${{ number_format($caja->monto_inicial + $caja->total_ingresos - $caja->total_egresos, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div id="cajaInfoDetalle" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-xl hidden">
            <div class="flex items-center gap-2 text-sm text-green-700">
                <span>🏦</span>
                <span class="font-medium" id="cajaNombre"></span>
                <span class="text-green-400">|</span>
                <span>👤 <span id="cajaUsuario"></span></span>
                <span class="text-green-400">|</span>
                <span>💰 $<span id="cajaSaldo"></span></span>
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
            <span>💰 ${{ number_format($cajaAbierta->monto_inicial + $cajaAbierta->total_ingresos - $cajaAbierta->total_egresos, 2) }}</span>
        </div>
    </div>
@else
    <div class="p-3 mb-4 bg-yellow-50 border border-yellow-200 rounded-xl">
        <p class="text-sm text-yellow-700">⚠️ No hay caja abierta en esta sucursal</p>
    </div>
@endif

<div class="flex justify-end mb-4">
    @can('crear_ventas')
    <a href="{{ route('ventas.index') }}" 
       class="px-4 py-2 text-sm font-medium text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
        + Nueva Cotización
    </a>
    @endcan
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
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Productos</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Usuario</th>
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
                        <div>
                            <span class="font-medium">{{ $cotizacion->cliente->nombre ?? 'Cliente mostrador' }}</span>
                            @if($cotizacion->cliente && $cotizacion->cliente->telefono)
                                <p class="text-xs text-gray-400">📱 {{ $cotizacion->cliente->telefono }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-center">{{ $cotizacion->fecha_cotizacion->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm text-center">
                        @if($cotizacion->fecha_validez)
                            <span class="{{ $cotizacion->fecha_validez->isPast() ? 'text-red-600 font-medium' : 'text-green-600' }}">
                                {{ $cotizacion->fecha_validez->format('d/m/Y') }}
                                @if($cotizacion->fecha_validez->isPast())
                                    <span class="block text-xs">Vencida</span>
                                @else
                                    <span class="block text-xs">Vigente</span>
                                @endif
                            </span>
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-bold text-right text-indigo-600">${{ number_format($cotizacion->total, 2) }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
                            {{ $cotizacion->detalles->count() }} prod.
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-center">{{ $cotizacion->usuario->name }}</td>
                    <td class="px-6 py-4 text-center">
                        @switch($cotizacion->estado)
                            @case('activa') <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">🟢 Activa</span> @break
                            @case('convertida') <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">🔄 Convertida</span> @break
                            @case('vencida') <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">⏰ Vencida</span> @break
                            @case('cancelada') <span class="px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded-full">❌ Cancelada</span> @break
                            @default <span class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">{{ $cotizacion->estado }}</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @can('crear_ventas')
                                @if($cotizacion->estado == 'activa')
                                <button type="button" onclick="cargarAlCarrito({{ $cotizacion->id }})"
                                    class="p-2 text-gray-400 transition hover:text-green-600" title="Cargar al carrito">🛒</button>
                                @endif
                            @endcan
                            
                            @can('ver_cotizaciones')
                            <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" target="_blank"
                               class="p-2 text-gray-400 transition hover:text-red-600" title="Descargar PDF">📄</a>
                            <a href="{{ route('cotizaciones.show', $cotizacion) }}"
                               class="p-2 text-gray-400 transition hover:text-indigo-600" title="Ver detalle">👁️</a>
                            @endcan
                            
                            @can('cancelar_cotizaciones')
                                @if($cotizacion->estado == 'activa')
                                <button type="button" onclick="cancelarCotizacion({{ $cotizacion->id }})"
                                    class="p-2 text-gray-400 transition hover:text-red-600" title="Cancelar">❌</button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                        No hay cotizaciones registradas
                        @can('crear_ventas')
                        <div class="mt-2">
                            <a href="{{ route('ventas.index') }}" class="text-indigo-600 hover:text-indigo-800">+ Crear primera cotización</a>
                        </div>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t">{{ $cotizaciones->links() }}</div>
</div>

{{-- ✅ UN SOLO SCRIPT --}}
<script>
// ==================== INICIALIZACIÓN ÚNICA ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ [COTIZACIONES] Inicializando...');
    
    // Verificar Axios
    if (typeof axios === 'undefined') {
        console.error('❌ Axios no disponible');
        return;
    }
    
    // Configurar Axios
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    // Inicializar selector de caja
    const select = document.getElementById('cajaActivaSelect');
    if (select) {
        const savedCaja = localStorage.getItem('cajaActivaSeleccionada');
        if (savedCaja && select.querySelector(`option[value="${savedCaja}"]`)) {
            select.value = savedCaja;
            // Solo actualizar info visual, sin guardar de nuevo
            actualizarInfoCajaVisual();
        } else {
            actualizarInfoCaja();
        }
    }
    
    console.log('✅ [COTIZACIONES] Inicializado');
});

// ==================== FUNCIONES ====================

function actualizarInfoCaja() {
    const select = document.getElementById('cajaActivaSelect');
    if (!select) return;
    
    const option = select.options[select.selectedIndex];
    const nombre = option.getAttribute('data-nombre');
    const usuario = option.getAttribute('data-usuario');
    const saldo = parseFloat(option.getAttribute('data-saldo') || 0);
    
    document.getElementById('cajaNombre').textContent = nombre;
    document.getElementById('cajaUsuario').textContent = usuario;
    document.getElementById('cajaSaldo').textContent = saldo.toFixed(2);
    document.getElementById('cajaInfoDetalle').classList.remove('hidden');
    
    localStorage.setItem('cajaActivaSeleccionada', select.value);
    
    // ✅ Guardar en sesión SIN recargar
    axios.post('{{ route("cotizaciones.caja.seleccionar") }}', { 
        caja_apertura_id: select.value 
    })
    .then(() => console.log('✅ Caja guardada en sesión'))
    .catch(() => {}); // Ignorar error silenciosamente
    
    console.log('🏦 Caja seleccionada:', { id: select.value, nombre, usuario, saldo });
}

async function cargarAlCarrito(id) {
    @if(!auth()->user()->can('crear_ventas'))
        Swal.fire({ icon: 'error', title: 'Acceso denegado', text: 'No tienes permisos para realizar ventas.', confirmButtonColor: '#ef4444' });
        return;
    @endif
    
    const { isConfirmed } = await Swal.fire({
        title: '¿Cargar cotización al carrito?',
        text: 'El carrito actual será reemplazado.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cargar',
        cancelButtonText: 'Cancelar'
    });
    
    if (!isConfirmed) return;
    
    Swal.fire({ title: 'Cargando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const response = await axios.post(`/cotizaciones/${id}/cargar-carrito`);
        if (response.data.success) {
            await Swal.fire({
                icon: 'success',
                title: '¡Carrito cargado!',
                html: `Se cargaron <strong>${response.data.total_items || response.data.items?.length || 0}</strong> productos.`,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Ir a vender'
            });
            window.location.href = '{{ route("ventas.index") }}';
        }
    } catch (error) {
        const msg = error.response?.data?.message || 'Error al cargar';
        await Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    }
}

async function cancelarCotizacion(id) {
    @if(!auth()->user()->can('cancelar_cotizaciones'))
        Swal.fire({ icon: 'error', title: 'Acceso denegado', text: 'No tienes permisos.', confirmButtonColor: '#ef4444' });
        return;
    @endif
    
    const { isConfirmed } = await Swal.fire({
        title: '¿Cancelar cotización?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
    });
    
    if (!isConfirmed) return;
    
    Swal.fire({ title: 'Cancelando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const response = await axios.delete(`/cotizaciones/${id}`);
        if (response.data.success) {
            await Swal.fire({ icon: 'success', title: 'Cancelada', timer: 2000 });
            location.reload();
        }
    } catch (error) {
        const msg = error.response?.data?.message || 'Error al cancelar';
        await Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    }
}
// ✅ Función solo visual (no guarda)
function actualizarInfoCajaVisual() {
    const select = document.getElementById('cajaActivaSelect');
    if (!select) return;
    
    const option = select.options[select.selectedIndex];
    document.getElementById('cajaNombre').textContent = option.getAttribute('data-nombre');
    document.getElementById('cajaUsuario').textContent = option.getAttribute('data-usuario');
    document.getElementById('cajaSaldo').textContent = parseFloat(option.getAttribute('data-saldo') || 0).toFixed(2);
    document.getElementById('cajaInfoDetalle').classList.remove('hidden');
}

// ✅ Función completa (guarda en sesión) - solo al cambiar manualmente
function actualizarInfoCaja() {
    actualizarInfoCajaVisual();
    
    const select = document.getElementById('cajaActivaSelect');
    if (!select) return;
    
    localStorage.setItem('cajaActivaSeleccionada', select.value);
    
    axios.post('{{ route("cotizaciones.caja.seleccionar") }}', { 
        caja_apertura_id: select.value 
    }).then(() => {
        console.log('✅ Caja guardada, recargando...');
        location.reload(); // ✅ RECARGAR para aplicar filtro
    })
    .catch(() => {
        // Si falla, recargar de todos modos
        location.reload();
    });
    
    console.log('🏦 Caja seleccionada:', { id: select.value });
}
</script>
@endsection