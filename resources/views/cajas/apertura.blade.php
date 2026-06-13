{{-- resources/views/cajas/apertura.blade.php --}}
@extends('layouts.app')

@section('title', 'Apertura de Caja')
@section('page-title', 'Apertura de Caja')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-alert type="error" :message="session('error')" />
    <x-alert type="success" :message="session('success')" />

    @if($errors->any())
    <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
        <ul class="space-y-1 text-sm text-red-600 list-disc list-inside">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    {{-- 🔥 SECCIÓN: CAJAS ANTERIORES PENDIENTES --}}
    @if(isset($cajasAnteriores) && $cajasAnteriores->count() > 0)
    <div class="p-6 mb-6 border-l-4 border-yellow-500 shadow-md bg-yellow-50 rounded-xl">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-yellow-800">⚠️ Cajas pendientes de cierre</h3>
                <p class="mt-1 text-sm text-yellow-700">
                    Existen cajas abiertas de días anteriores que deben cerrarse antes de abrir nuevas cajas.
                </p>
                <div class="mt-3 space-y-2">
                    @foreach($cajasAnteriores as $cajaAntigua)
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                        <div>
                            <p class="font-medium text-gray-800">
                                {{ $cajaAntigua->caja->nombre }}
                                <span class="text-xs text-gray-500">({{ $cajaAntigua->caja->codigo }})</span>
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Abierta por: {{ $cajaAntigua->usuario->name }} |
                                Fecha: {{ $cajaAntigua->fecha->format('d/m/Y') }} |
                                Hora: {{ $cajaAntigua->created_at->format('H:i') }}
                            </p>
                            <p class="mt-1 text-xs font-medium text-indigo-600">
                                Saldo:
                                ${{ number_format($cajaAntigua->monto_inicial + $cajaAntigua->total_ingresos - $cajaAntigua->total_egresos, 2) }}
                            </p>
                        </div>
                        @can('cerrar_caja')
                        <button onclick="cerrarCaja({{ $cajaAntigua->id }}, '{{ $cajaAntigua->caja->nombre }}', {{ $cajaAntigua->monto_inicial + $cajaAntigua->total_ingresos - $cajaAntigua->total_egresos }}, {{ $cajaAntigua->user_id }})"
                                class="px-3 py-1.5 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Cerrar esta caja
                        </button>
                        @endcan
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Cajas ya abiertas --}}
    @if($aperturasActivas->count() > 0)
    <div class="p-6 mb-6 bg-white shadow-lg rounded-3xl">
        <h2 class="mb-4 text-xl font-bold text-slate-800">📋 Cajas Abiertas</h2>
        <div class="space-y-3">
            @foreach($aperturasActivas as $apertura)
            <div class="flex items-center justify-between p-4 border rounded-xl bg-green-50">
                <div>
                    <p class="font-semibold text-slate-800">{{ $apertura->caja->nombre }} ({{ $apertura->caja->codigo }})</p>
                    <p class="text-sm text-gray-500">
                        Abierta por: {{ $apertura->usuario->name }} | {{ $apertura->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Monto inicial: ${{ number_format($apertura->monto_inicial, 2) }} |
                        Ingresos: +${{ number_format($apertura->total_ingresos, 2) }} |
                        Egresos: -${{ number_format($apertura->total_egresos, 2) }}
                    </p>
                    <p class="text-sm font-semibold text-indigo-600">
                        Saldo actual: ${{ number_format($apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos, 2) }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('cajas.operaciones') }}" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Operaciones</a>
                    @can('cerrar_caja')
                        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Administrador') || $apertura->user_id == auth()->id())
                        <button onclick="cerrarCaja({{ $apertura->id }}, '{{ $apertura->caja->nombre }}', {{ $apertura->monto_inicial + $apertura->total_ingresos - $apertura->total_egresos }}, {{ $apertura->user_id }})"
                                class="px-4 py-2 text-sm text-white bg-red-600 rounded-xl hover:bg-red-700">Cerrar</button>
                        @endif
                    @endcan
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Cajas disponibles para abrir --}}
    @if($cajasDisponibles->count() > 0)
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">🔓</div>
            <h2 class="text-2xl font-bold text-slate-800">Abrir Nueva Caja</h2>
            <p class="mt-2 text-gray-500">Cajas disponibles: {{ $cajasDisponibles->count() }}</p>
        </div>

        {{-- Visualizar cajas disponibles --}}
        <div class="grid grid-cols-1 gap-3 mb-6 md:grid-cols-2">
            @foreach($cajasDisponibles as $caja)
            <div class="p-4 border rounded-xl bg-gray-50">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🏦</span>
                    <div>
                        <p class="font-semibold">{{ $caja->nombre }}</p>
                        <p class="text-xs text-gray-500">Código: {{ $caja->codigo }}</p>
                        <p class="text-xs text-gray-500">Sucursal: {{ $caja->sucursal->nombre ?? 'N/A' }}</p>
                        @if($caja->permite_multiple)
                            <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">Permite múltiple</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <form id="formAbrirCaja" onsubmit="return abrirCaja(event)">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Caja *</label>
                    <select name="caja_id" id="caja_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar caja...</option>
                        @foreach($cajasDisponibles as $caja)
                            <option value="{{ $caja->id }}">{{ $caja->nombre }} ({{ $caja->codigo }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Monto Inicial *</label>
                    <input type="number" name="monto_inicial" id="monto_inicial" value="0.00" min="0" step="0.01" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500" placeholder="Notas adicionales..."></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                @can('abrir_caja')
                <button type="submit" id="btnAbrirCaja" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">🔓 Abrir caja</button>
                @endcan
            </div>
        </form>
    </div>
    @else
    <div class="p-8 text-center bg-white shadow-lg rounded-3xl">
        <div class="mb-4 text-6xl">📦</div>
        <h2 class="mb-2 text-xl font-bold text-slate-800">No hay cajas disponibles</h2>
        <p class="text-gray-500">Todas las cajas están actualmente en uso o no hay cajas configuradas.</p>
    </div>
    @endif
</div>

{{-- ✅ MODAL CERRAR CAJA CON DATOS --}}
<div id="modalCerrarCaja" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50">
    <div class="w-full max-w-md p-6 mx-4 bg-white rounded-2xl">
        <h3 class="mb-4 text-xl font-bold">🔒 Cerrar Caja</h3>
        <div class="p-4 mb-4 space-y-2 bg-gray-50 rounded-xl">
            <div class="flex justify-between">
                <span class="text-gray-600">Caja:</span>
                <span class="font-semibold" id="infoCajaNombre">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Saldo actual:</span>
                <span class="font-bold text-indigo-600" id="infoSaldoActual">$0.00</span>
            </div>
        </div>
        <form id="formCerrarCaja" method="POST" onsubmit="return confirmarCierre(event)">
            @csrf
            <input type="hidden" name="apertura_id" id="cerrar_apertura_id">
            <input type="hidden" name="caja_user_id" id="caja_user_id">
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium">Monto Final *</label>
                    <input type="number" name="monto_final" id="monto_final" required min="0" step="0.01"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Ingresa el monto físico que queda en caja</p>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="w-full px-4 py-3 border rounded-xl"></textarea>
                </div>
                <div id="passwordSection" style="display:none;">
                    <div class="p-3 border border-yellow-200 bg-yellow-50 rounded-xl">
                        <p class="mb-2 text-sm text-yellow-800">⚠️ Estás cerrando una caja de otro usuario. Ingresa tu contraseña maestra:</p>
                        <input type="password" name="password_maestra" id="password_maestra"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                            placeholder="Contraseña maestra">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="cerrarModalCierre()" class="px-4 py-2 border rounded-xl hover:bg-gray-50">Cancelar</button>
                <button type="submit" id="btnCerrarCaja" class="px-4 py-2 text-white bg-red-600 rounded-xl hover:bg-red-700">🔒 Cerrar Caja</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
const currentUserId = {{ auth()->id() }};
const isSuperAdmin = {{ auth()->user()->hasRole('Super Admin') ? 'true' : 'false' }};
const isAdmin = {{ auth()->user()->hasRole('Administrador') ? 'true' : 'false' }};

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
axios.defaults.headers.common['Accept'] = 'application/json';

async function abrirCaja(event) {
    event.preventDefault();
    const btn = document.getElementById('btnAbrirCaja');
    const cajaId = document.getElementById('caja_id').value;
    const montoInicial = document.getElementById('monto_inicial').value;
    const observaciones = document.getElementById('observaciones').value;

    if (!cajaId) { Swal.fire({ icon: 'warning', title: 'Selecciona una caja', confirmButtonColor: '#4f46e5' }); return false; }

    const { isConfirmed } = await Swal.fire({
        title: '¿Abrir caja?', icon: 'question', showCancelButton: true,
        confirmButtonColor: '#10b981', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, abrir', cancelButtonText: 'Cancelar'
    });
    if (!isConfirmed) return false;

    btn.disabled = true;
    Swal.fire({ title: 'Abriendo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res = await axios.post('{{ route("cajas.abrir") }}', { caja_id: cajaId, monto_inicial: montoInicial, observaciones });
        if (res.data?.success) {
            await Swal.fire({ icon: 'success', title: '¡Caja abierta!', timer: 2000 });
            window.location.href = res.data.redirect || '{{ route("cajas.operaciones") }}';
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
    } finally { btn.disabled = false; }
    return false;
}

function cerrarCaja(id, nombre, saldo, userId) {
    document.getElementById('cerrar_apertura_id').value = id;
    document.getElementById('caja_user_id').value = userId;
    document.getElementById('infoCajaNombre').textContent = nombre;
    document.getElementById('infoSaldoActual').textContent = '$' + saldo.toFixed(2);
    document.getElementById('monto_final').value = saldo.toFixed(2);

    const passwordSection = document.getElementById('passwordSection');
    const passwordInput = document.getElementById('password_maestra');

    if ((isSuperAdmin || isAdmin) && userId != currentUserId) {
        passwordSection.style.display = 'block';
        passwordInput.required = true;
    } else {
        passwordSection.style.display = 'none';
        passwordInput.required = false;
        passwordInput.value = '';
    }

    document.getElementById('modalCerrarCaja').classList.remove('hidden');
    document.getElementById('modalCerrarCaja').classList.add('flex');
}

function cerrarModalCierre() {
    document.getElementById('modalCerrarCaja').classList.add('hidden');
    document.getElementById('modalCerrarCaja').classList.remove('flex');
    document.getElementById('formCerrarCaja').reset();
    document.getElementById('passwordSection').style.display = 'none';
}

async function confirmarCierre(event) {
    event.preventDefault();
    const form = document.getElementById('formCerrarCaja');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const btn = document.getElementById('btnCerrarCaja');

    if (!data.monto_final || parseFloat(data.monto_final) < 0) {
        Swal.fire({ icon: 'warning', title: 'Monto requerido', text: 'Ingresa el monto final', confirmButtonColor: '#4f46e5' });
        return false;
    }

    const passwordSection = document.getElementById('passwordSection');
    if (passwordSection.style.display !== 'none' && !data.password_maestra) {
        Swal.fire({ icon: 'warning', title: 'Contraseña requerida', text: 'Ingresa tu contraseña maestra', confirmButtonColor: '#4f46e5' });
        return false;
    }

    const { isConfirmed } = await Swal.fire({
        title: '¿Cerrar caja?',
        html: `<div class="text-left">
            <p>Caja: <strong>${document.getElementById('infoCajaNombre').textContent}</strong></p>
            <p>Monto final: <strong>$${parseFloat(data.monto_final).toFixed(2)}</strong></p>
            <p class="mt-2 text-red-600">Esta acción no se puede deshacer</p>
        </div>`,
        icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cerrar', cancelButtonText: 'Cancelar'
    });
    if (!isConfirmed) return false;

    btn.disabled = true;
    Swal.fire({ title: 'Cerrando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res = await axios.post('{{ route("cajas.cerrar") }}', data);
        if (res.data?.success) {
            await Swal.fire({ icon: 'success', title: '¡Caja cerrada!', html: res.data.message, timer: 2500 });
            window.location.href = res.data.redirect || '{{ route("cajas.apertura") }}';
        } else {
            throw new Error(res.data.message || 'Error');
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.response?.data?.message || 'Error', confirmButtonColor: '#ef4444' });
    } finally {
        btn.disabled = false;
    }
    return false;
}
</script>
@endpush
@endsection