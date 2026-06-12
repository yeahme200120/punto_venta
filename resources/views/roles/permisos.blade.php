@extends('layouts.app')

@section('title', 'Permisos del rol: ' . $role->name)
@section('page-title', 'Permisos: ' . $role->name)
@section('breadcrumbs')
<li><span class="text-gray-400">/</span></li>
<li><a href="{{ route('roles.index') }}" class="text-gray-500 hover:text-indigo-600">Roles</a></li>
<li><span class="text-gray-400">/</span></li>
<li><span class="font-medium text-gray-700">{{ $role->name }}</span></li>
<li><span class="text-gray-400">/</span></li>
<li><span class="font-medium text-gray-700">Permisos</span></li>
@endsection

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="p-4 mb-4 border border-yellow-200 bg-yellow-50 rounded-xl">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <div>
                <p class="text-sm font-medium text-yellow-800">⚠️ Importante</p>
                <p class="text-xs text-yellow-700">Al modificar los permisos de este rol, <strong>TODOS los usuarios con
                        el rol "{{ $role->name }}"</strong> serán cerrados y deberán volver a iniciar sesión para que
                    los cambios surtan efecto.</p>
            </div>
        </div>
    </div>
    <form action="{{ route('roles.permisos_rol.update', $role) }}" method="POST" id="permisosForm">
        @csrf @method('PUT')

        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
            <div class="p-4 border-b bg-gray-50">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔐</span>
                        <h3 class="font-semibold text-gray-800">Permisos del rol "{{ $role->name }}"</h3>
                        <span class="px-2 py-1 text-xs text-gray-400 bg-white rounded-full shadow-sm">
                            {{ $permisosAgrupados->flatten()->count() }} permisos disponibles
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="marcarTodos"
                            class="px-3 py-1.5 text-xs bg-green-500 text-white rounded-lg hover:bg-green-600">✓
                            Todos</button>
                        <button type="button" id="desmarcarTodos"
                            class="px-3 py-1.5 text-xs bg-red-500 text-white rounded-lg hover:bg-red-600">✕
                            Ninguno</button>
                    </div>
                </div>
            </div>

            {{-- Buscador --}}
            <div class="p-4 bg-white border-b">
                <div class="relative">
                    <svg class="absolute w-4 h-4 text-gray-400 left-3 top-2.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="buscarPermiso" placeholder="Buscar permiso..."
                        class="w-full py-2 pr-4 text-sm border rounded-lg pl-9 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- Lista de permisos por módulo --}}
            <div class="p-4 max-h-[500px] overflow-y-auto">
                <div class="space-y-4">
                    @foreach($permisosAgrupados as $modulo => $items)
                    <div class="overflow-hidden border modulo-card rounded-xl">
                        <div class="flex items-center justify-between p-3 cursor-pointer bg-gray-50 modulo-header">
                            <div class="flex items-center gap-2">
                                <span class="text-lg modulo-icon">
                                    @switch($modulo)
                                    @case('Dashboard') 📊 @break
                                    @case('Empresas') 🏢 @break
                                    @case('Licencias') 📜 @break
                                    @case('Inventario') 📦 @break
                                    @case('Compras') 🛒 @break
                                    @case('Proveedores') 🚚 @break
                                    @case('Ventas') 💰 @break
                                    @case('Facturacion') 🧾 @break
                                    @case('Clientes') 👥 @break
                                    @case('Caja') 💵 @break
                                    @case('Cobranza') 📋 @break
                                    @case('FormasPago') 💳 @break
                                    @case('Notificaciones') 🔔 @break
                                    @case('Impresoras') 🖨️ @break
                                    @case('Ticket') 🎫 @break
                                    @case('Usuarios') 🔐 @break
                                    @case('Roles') 🎭 @break
                                    @case('Reportes') 📈 @break
                                    @case('Respaldos') 💾 @break
                                    @case('Insumos') 📦 @break
                                    @case('UnidadesMedida') 📏 @break
                                    @default 📌
                                    @endswitch
                                </span>
                                <span class="text-sm font-semibold text-gray-700 uppercase">{{ $modulo }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1 text-xs text-gray-500 cursor-pointer select-none">
                                    <input type="checkbox"
                                        class="modulo-select-all w-3.5 h-3.5 rounded border-gray-300">
                                    <span class="ml-1">Seleccionar todos</span>
                                </label>
                                <span class="text-xs text-gray-400 permisos-count">{{ $items->count() }} permisos</span>
                                <button type="button" class="text-gray-400 modulo-toggle hover:text-gray-600">
                                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 p-3 modulo-content md:grid-cols-3 lg:grid-cols-4">
                            @foreach($items as $permiso)
                            @php $checked = in_array($permiso->name, $permisosDelRol); @endphp
                            <label
                                class="permiso-item flex items-center gap-2 p-1.5 rounded-lg hover:bg-indigo-50 cursor-pointer transition"
                                data-permiso="{{ strtolower($permiso->name) }}">
                                <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}"
                                    class="permiso-check w-3.5 h-3.5 text-indigo-600 rounded border-gray-300 focus:ring-2 focus:ring-indigo-500"
                                    {{ $checked ? 'checked' : '' }}>
                                <span class="text-xs text-gray-600">
                                    @php
                                    $nombre = str_replace('_', ' ', $permiso->name);
                                    $partes = explode(' ', $nombre);
                                    $accion = $partes[0] ?? '';
                                    $resto = implode(' ', array_slice($partes, 1));
                                    @endphp
                                    <span class="font-semibold 
                                        @if($accion == 'ver') text-blue-600
                                        @elseif($accion == 'crear') text-green-600
                                        @elseif($accion == 'editar') text-amber-600
                                        @elseif($accion == 'eliminar') text-red-600
                                        @else text-gray-600 @endif">
                                        {{ ucfirst($accion) }}
                                    </span>
                                    {{ $resto }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Resumen --}}
            <div class="flex items-center justify-between p-4 text-sm border-t bg-gray-50">
                <span class="text-gray-500">
                    <span id="seleccionadosCount">0</span> de <span id="totalPermisos">{{
                        $permisosAgrupados->flatten()->count() }}</span> permisos seleccionados
                </span>
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-6">
            <a href="{{ route('roles.index') }}"
                class="px-6 py-2.5 border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" id="submitBtn"
                class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition shadow-md">
                💾 Guardar permisos
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        
        // Elementos
        const marcarTodosBtn = document.getElementById('marcarTodos');
        const desmarcarTodosBtn = document.getElementById('desmarcarTodos');
        const buscarInput = document.getElementById('buscarPermiso');
        const seleccionadosSpan = document.getElementById('seleccionadosCount');
        const totalPermisosSpan = document.getElementById('totalPermisos');
        const form = document.getElementById('permisosForm');
        const submitBtn = document.getElementById('submitBtn');

        function actualizarContador() {
            const checks = document.querySelectorAll('input[name="permisos[]"]');
            const seleccionados = Array.from(checks).filter(cb => cb.checked).length;
            if (seleccionadosSpan) seleccionadosSpan.textContent = seleccionados;
        }

        function actualizarSelectAllModulos() {
            document.querySelectorAll('.modulo-card').forEach(moduloCard => {
                const checks = moduloCard.querySelectorAll('.permiso-check');
                const selectAll = moduloCard.querySelector('.modulo-select-all');
                if (selectAll && checks.length) {
                    const todosChequeados = Array.from(checks).every(c => c.checked);
                    selectAll.checked = todosChequeados;
                    selectAll.indeterminate = !todosChequeados && Array.from(checks).some(c => c.checked);
                }
            });
        }

        // Marcar/Desmarcar todos
        if (marcarTodosBtn) {
            marcarTodosBtn.addEventListener('click', () => {
                document.querySelectorAll('input[name="permisos[]"]').forEach(cb => cb.checked = true);
                actualizarContador();
                actualizarSelectAllModulos();
            });
        }
        if (desmarcarTodosBtn) {
            desmarcarTodosBtn.addEventListener('click', () => {
                document.querySelectorAll('input[name="permisos[]"]').forEach(cb => cb.checked = false);
                actualizarContador();
                actualizarSelectAllModulos();
            });
        }

        // Seleccionar todos por módulo
        document.querySelectorAll('.modulo-select-all').forEach(selectAll => {
            selectAll.addEventListener('change', function() {
                const moduloCard = this.closest('.modulo-card');
                const checks = moduloCard.querySelectorAll('.permiso-check');
                checks.forEach(cb => cb.checked = this.checked);
                actualizarContador();
                actualizarSelectAllModulos();
            });
        });

        // Actualizar cuando cambian permisos individuales
        document.querySelectorAll('.permiso-check').forEach(cb => {
            cb.addEventListener('change', function() {
                const moduloCard = this.closest('.modulo-card');
                const checks = moduloCard.querySelectorAll('.permiso-check');
                const selectAll = moduloCard.querySelector('.modulo-select-all');
                if (selectAll) {
                    const todosChequeados = Array.from(checks).every(c => c.checked);
                    selectAll.checked = todosChequeados;
                    selectAll.indeterminate = !todosChequeados && Array.from(checks).some(c => c.checked);
                }
                actualizarContador();
            });
        });

        // Buscador
        if (buscarInput) {
            buscarInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                document.querySelectorAll('.permiso-item').forEach(item => {
                    const texto = item.getAttribute('data-permiso') || '';
                    item.style.display = term === '' || texto.includes(term) ? '' : 'none';
                });
                document.querySelectorAll('.modulo-card').forEach(modulo => {
                    const visibles = modulo.querySelectorAll('.permiso-item:not([style*="display: none"])');
                    modulo.style.display = visibles.length > 0 ? '' : 'none';
                });
            });
        }

        // Toggle módulos
        document.querySelectorAll('.modulo-header').forEach(header => {
            header.addEventListener('click', (e) => {
                if (e.target.type !== 'checkbox' && !e.target.closest('.modulo-select-all') && !e.target.closest('.modulo-toggle')) {
                    const content = header.nextElementSibling;
                    const icon = header.querySelector('.modulo-toggle svg');
                    if (content) {
                        content.classList.toggle('hidden');
                        if (icon) icon.classList.toggle('rotate-180');
                    }
                }
            });
        });

        // Envío del formulario con confirmación
if (form) {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const confirm = await Swal.fire({
            title: '⚠️ ¿Guardar cambios?',
            html: `<div class="text-left">
                <p class="mb-2">Estás a punto de modificar los permisos del rol <strong>{{ $role->name }}</strong>.</p>
                <p class="font-semibold text-red-600">⚠️ IMPORTANTE:</p>
                <p>Todos los usuarios con este rol serán cerrados y deberán volver a iniciar sesión.</p>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        });
        
        if (confirm.isConfirmed) {
            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            submitBtn.disabled = true;
            
            try {
                const formData = new FormData(form);
                // ✅ Usar POST con método override en lugar de PUT directo
                const response = await axios.post(form.action, formData, {
                    headers: {
                        'X-HTTP-Method-Override': 'PUT'
                    }
                });
                
                if (response.data.success || response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Permisos actualizados',
                        text: response.data.message || 'Los permisos han sido actualizados correctamente.',
                        confirmButtonText: 'Cerrar'
                    }).then(() => {
                        window.location.href = '{{ route("roles.index") }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.data.message || 'Error al guardar',
                        confirmButtonText: 'Cerrar'
                    });
                    submitBtn.disabled = false;
                }
            } catch (error) {
                let mensaje = 'Error al guardar los permisos';
                if (error.response?.data?.message) {
                    mensaje = error.response.data.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje,
                    confirmButtonText: 'Cerrar'
                });
                submitBtn.disabled = false;
            }
        }
    });
}

        // Inicializar
        actualizarContador();
        actualizarSelectAllModulos();
    });
</script>
@endpush

<style>
    .modulo-content.hidden {
        display: none;
    }

    .rotate-180 {
        transform: rotate(180deg);
    }

    .modulo-header {
        cursor: pointer;
    }

    .permiso-item:hover {
        background-color: #eef2ff;
    }
</style>
@endsection