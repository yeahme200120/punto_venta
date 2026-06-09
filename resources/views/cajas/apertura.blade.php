@extends('layouts.app')

@section('title', 'Apertura de Caja')
@section('page-title', 'Apertura de Caja')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cajas.cajas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Cajas
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Apertura</span>
    </li>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- MOSTRAR TODAS LAS CAJAS ABIERTAS (SUPER ADMIN) --}}
        @if($aperturasActivas && $aperturasActivas->count() > 0)
            <div class="mb-6">
                <h3 class="mb-3 text-lg font-semibold text-gray-700">📋 Cajas Abiertas</h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($aperturasActivas as $apertura)
                        <div class="overflow-hidden bg-white border shadow-sm rounded-xl">
                            <div class="p-4 {{ $apertura->user_id == auth()->id() ? 'border-l-4 border-green-500' : '' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                        🟢 ABIERTA
                                    </span>
                                    @if($apertura->user_id == auth()->id())
                                        <span class="px-2 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-full">
                                            TU CAJA
                                        </span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-gray-800">{{ $apertura->caja->nombre }}</h4>
                                <p class="text-sm text-gray-500">Usuario: {{ $apertura->usuario->name }}</p>
                                <p class="text-sm text-gray-500">Apertura: {{ $apertura->fecha_apertura->format('d/m/Y H:i') }}</p>
                                <p class="text-sm font-semibold text-green-600">Monto inicial:
                                    ${{ number_format($apertura->monto_inicial, 2) }}</p>

                                <div class="flex gap-2 mt-3">
                                    @if($apertura->user_id == auth()->id())
                                        <a href="{{ route('cajas.operaciones') }}"
                                            class="flex-1 px-3 py-2 text-sm text-center text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                            Operar
                                        </a>
                                        <button type="button" 
                                            onclick="mostrarModalCierre({{ $apertura->id }})"
                                            class="flex-1 px-3 py-2 text-sm text-center text-white bg-red-600 rounded-lg hover:bg-red-700">
                                            Cerrar
                                        </button>
                                    @elseif(auth()->user()->hasRole('Super Admin'))
                                        <button type="button"
                                            onclick="mostrarModalCierreSuperAdmin({{ $apertura->id }}, '{{ $apertura->usuario->name }}', '{{ $apertura->caja->nombre }}')"
                                            class="flex-1 px-3 py-2 text-sm text-center text-white bg-red-600 rounded-lg hover:bg-red-700">
                                            🔒 Cerrar caja
                                        </button>
                                        <a href="{{ route('cajas.verApertura', $apertura->id) }}"
                                            class="flex-1 px-3 py-2 text-sm text-center text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                                            Ver detalles
                                        </a>
                                    @else
                                        <a href="{{ route('cajas.verApertura', $apertura->id) }}"
                                            class="flex-1 px-3 py-2 text-sm text-center text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                                            Ver detalles
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CAJA PENDIENTE DE CIERRE --}}
        @if($aperturaAnterior && !$tieneAperturaPropia)
            <div class="p-8 mb-6 text-center border border-yellow-200 bg-yellow-50 rounded-3xl">
                <div class="mb-4 text-5xl">⚠️</div>
                <h3 class="mb-2 text-xl font-bold text-yellow-800">Caja pendiente de cierre</h3>
                <p class="text-yellow-700">Tienes una caja abierta del día {{ $aperturaAnterior->fecha->format('d/m/Y') }}.</p>
                <p class="mt-2 text-yellow-700">Debes cerrarla antes de abrir una nueva.</p>
                <div class="inline-block p-4 mt-4 bg-white rounded-xl">
                    <p class="text-sm text-gray-500">Caja: <span class="font-semibold">{{ $aperturaAnterior->caja->nombre }}</span></p>
                    <p class="text-sm text-gray-500">Apertura: <span class="font-semibold">{{ $aperturaAnterior->fecha_apertura->format('d/m/Y H:i') }}</span></p>
                </div>
                <div class="mt-6">
                    <button type="button" onclick="mostrarModalCierre({{ $aperturaAnterior->id }})"
                        class="px-6 py-2 text-white transition bg-red-600 rounded-xl hover:bg-red-700">
                        Cerrar caja pendiente
                    </button>
                </div>
            </div>
        @endif

        {{-- FORMULARIO PARA ABRIR NUEVA CAJA --}}
        @if(!$tieneAperturaPropia || auth()->user()->hasRole('Super Admin'))
            <div class="p-8 bg-white shadow-lg rounded-3xl">
                <div class="mb-8 text-center">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 text-3xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-green-500 to-emerald-500">
                        💰
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">Abrir nueva caja</h2>
                    <p class="mt-2 text-gray-500">Selecciona la caja y el monto inicial</p>
                </div>

                <form id="formAbrirCaja" action="{{ route('cajas.abrir') }}" method="POST">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Seleccionar caja *</label>
                            <select name="caja_id" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Seleccionar caja --</option>
                                @foreach($cajasDisponibles as $caja)
                                    <option value="{{ $caja->id }}">
                                        {{ $caja->nombre }} ({{ $caja->codigo }})
                                        - Saldo: ${{ number_format($caja->saldo_actual, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @if($cajasDisponibles->isEmpty())
                                <p class="mt-2 text-sm text-red-600">No hay cajas disponibles para abrir.</p>
                            @endif
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Monto inicial (caja chica) *</label>
                            <div class="relative">
                                <span class="absolute text-gray-500 left-3 top-3">$</span>
                                <input type="number" name="monto_inicial" step="0.01" min="0" required
                                    class="w-full py-3 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea name="observaciones" rows="2" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </a>
                        <button type="submit" class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-green-600 to-emerald-500 rounded-xl hover:from-green-700 hover:to-emerald-600">
                            💰 Abrir caja
                        </button>
                    </div>
                </form>
            </div>
        @else
            @if($tieneAperturaPropia && !auth()->user()->hasRole('Super Admin'))
                <div class="p-8 text-center bg-gray-100 rounded-3xl">
                    <p class="text-gray-600">Ya tienes una caja abierta.</p>
                    <a href="{{ route('cajas.operaciones') }}" class="inline-block px-6 py-2 mt-3 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">
                        Ir a operaciones
                    </a>
                </div>
            @endif
        @endif
    </div>

    {{-- Modal de cierre para usuario normal - Mejorado --}}
    <div id="modalCierre" class="fixed inset-0 z-50 items-center justify-center hidden bg-black/50">
        <div class="w-full max-w-md p-6 bg-white shadow-2xl rounded-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">🔒 Cerrar caja</h3>
                <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="formCerrarCaja">
                @csrf
                <input type="hidden" name="apertura_id" id="apertura_id">
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Monto final en caja *</label>
                    <div class="relative">
                        <span class="absolute text-gray-500 left-3 top-3">$</span>
                        <input type="number" name="monto_final" id="monto_final" step="0.01" required
                            class="w-full py-2 pl-8 pr-4 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="Motivo del cierre (opcional)"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="cerrarModal()" 
                        class="px-4 py-2 text-gray-700 transition bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 text-white transition bg-red-600 rounded-lg hover:bg-red-700">
                        Confirmar cierre
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Estilos personalizados para SweetAlert en modo responsive */
        .custom-swal-container {
            width: 90%;
            max-width: 500px;
            padding: 0 !important;
        }
        .custom-swal-popup {
            border-radius: 1rem !important;
            padding: 1.5rem !important;
        }
        .custom-swal-input,
        .custom-swal-textarea {
            width: 100% !important;
            padding: 0.75rem !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            font-size: 0.95rem !important;
            margin-top: 0.25rem !important;
        }
        .custom-swal-input:focus,
        .custom-swal-textarea:focus {
            outline: none !important;
            border-color: #ef4444 !important;
            ring: 2px solid #ef4444 !important;
        }
        .custom-swal-label {
            display: block !important;
            text-align: left !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            color: #374151 !important;
            margin-bottom: 0.25rem !important;
        }
        .custom-swal-info {
            background-color: #fef3c7 !important;
            border-radius: 0.5rem !important;
            padding: 0.75rem !important;
            margin-bottom: 1rem !important;
        }
    </style>
    <script>
        // Mostrar mensajes flash con SweetAlert al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#10b981',
                    timer: 3000,
                    showConfirmButton: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#ef4444'
                });
            @endif
        });

        // Modal normal
        function mostrarModalCierre(aperturaId) {
            document.getElementById('apertura_id').value = aperturaId;
            document.getElementById('modalCierre').classList.remove('hidden');
            document.getElementById('modalCierre').classList.add('flex');
            // Limpiar campos
            document.getElementById('monto_final').value = '';
            document.getElementById('observaciones').value = '';
        }

        function cerrarModal() {
            document.getElementById('modalCierre').classList.add('hidden');
            document.getElementById('modalCierre').classList.remove('flex');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalCierre')?.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Envío AJAX del formulario normal
        document.getElementById('formCerrarCaja')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Procesando...',
                text: 'Cerrando caja, por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch('{{ route("cajas.cerrar") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: data.icon || 'success',
                        title: data.title || '¡Éxito!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: data.icon || 'error',
                        title: data.title || 'Error',
                        text: data.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: error.message || 'Ocurrió un error inesperado',
                    confirmButtonColor: '#ef4444'
                });
            }
        });

        // Super Admin: modal con SweetAlert mejorado
        function mostrarModalCierreSuperAdmin(aperturaId, usuarioNombre, cajaNombre) {
            Swal.fire({
                title: '⚠️ Cerrar caja de otro usuario',
                html: `
                    <div class="text-left">
                        <div class="custom-swal-info">
                            <p class="text-sm text-yellow-800">📌 <strong>Información de la caja:</strong></p>
                            <p class="mt-1 text-sm text-gray-700">🏦 Caja: <strong>${cajaNombre}</strong></p>
                            <p class="text-sm text-gray-700">👤 Usuario: <strong>${usuarioNombre}</strong></p>
                            <p class="mt-2 text-sm text-yellow-600">⚠️ Como Super Admin, estás cerrando una caja que no te pertenece.</p>
                        </div>
                        <div class="mb-4">
                            <label class="custom-swal-label">💰 Monto final en caja *</label>
                            <input type="number" id="swal_monto_final" step="0.01" class="custom-swal-input" placeholder="0.00">
                        </div>
                        <div class="mb-4">
                            <label class="custom-swal-label">📝 Observaciones (obligatorio) *</label>
                            <textarea id="swal_observaciones" rows="3" class="custom-swal-textarea" placeholder="Motivo del cierre..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="custom-swal-label">🔐 Contraseña maestra *</label>
                            <input type="password" id="swal_password" class="custom-swal-input" placeholder="Ingresa tu contraseña de administrador">
                        </div>
                    </div>
                `,
                icon: 'warning',
                width: '550px',
                showCancelButton: true,
                confirmButtonText: '✅ Confirmar cierre',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444',
                customClass: {
                    container: 'custom-swal-container',
                    popup: 'custom-swal-popup'
                },
                preConfirm: () => {
                    const montoFinal = document.getElementById('swal_monto_final').value;
                    const observaciones = document.getElementById('swal_observaciones').value;
                    const passwordMaestra = document.getElementById('swal_password').value;
                    
                    if (!montoFinal || parseFloat(montoFinal) < 0) {
                        Swal.showValidationMessage('❌ Ingresa un monto final válido');
                        return false;
                    }
                    if (!observaciones || observaciones.trim() === '') {
                        Swal.showValidationMessage('❌ Las observaciones son obligatorias para cerrar caja de otro usuario');
                        return false;
                    }
                    if (!passwordMaestra || passwordMaestra.trim() === '') {
                        Swal.showValidationMessage('❌ La contraseña maestra es obligatoria');
                        return false;
                    }
                    
                    return { 
                        monto_final: parseFloat(montoFinal), 
                        observaciones: observaciones, 
                        password_maestra: passwordMaestra 
                    };
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Cerrando caja, por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('apertura_id', aperturaId);
                    formData.append('monto_final', result.value.monto_final);
                    formData.append('observaciones', result.value.observaciones);
                    formData.append('password_maestra', result.value.password_maestra);

                    try {
                        const response = await fetch('{{ route("cajas.cerrar") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            Swal.fire({
                                icon: data.icon || 'success',
                                title: data.title || '¡Caja cerrada!',
                                text: data.message,
                                confirmButtonColor: '#10b981',
                                timer: 3000
                            }).then(() => {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: data.icon || 'error',
                                title: data.title || 'Error',
                                text: data.message,
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: error.message || 'Ocurrió un error inesperado',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            });
        }

        // Interceptar envío del formulario de apertura para mostrar SweetAlert
        document.getElementById('formAbrirCaja')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Procesando...',
                text: 'Abriendo caja, por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Caja abierta!',
                        text: data.message,
                        confirmButtonColor: '#10b981',
                        timer: 3000
                    }).then(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al abrir la caja',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: error.message || 'Ocurrió un error inesperado',
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    </script>
@endsection