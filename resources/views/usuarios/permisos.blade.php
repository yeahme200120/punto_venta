@extends('layouts.app')

@section('title', 'Permisos de ' . $usuario->name)
@section('page-title', 'Permisos: ' . $usuario->name)

@section('content')
<div class="mx-auto max-w-7xl">
    {{-- Info usuario --}}
    <div class="p-5 mb-5 bg-white border shadow-sm rounded-2xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center text-xl font-bold text-white rounded-full shadow-md w-14 h-14 bg-gradient-to-br from-indigo-600 to-cyan-500">
                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $usuario->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $usuario->email }}</p>
                    <div class="flex flex-wrap gap-3 mt-1 text-xs text-gray-500">
                        <span class="flex items-center gap-1">🏢 {{ $usuario->empresa->nombre ?? 'Sin empresa' }}</span>
                        @if($usuario->sucursal)
                        <span class="flex items-center gap-1">📍 {{ $usuario->sucursal->nombre }}</span>
                        @endif
                        <span class="flex items-center gap-1">{{ $usuario->activo ? '🟢 Activo' : '🔴 Inactivo' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('usuarios.permisos.update', $usuario) }}" method="POST" id="permisosForm">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            {{-- Panel de rol --}}
            <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
                <div class="p-4 border-b bg-gray-50">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🎭</span>
                        <h3 class="font-semibold text-gray-800">Rol principal (solo uno)</h3>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-3 p-2 transition rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="roles[]" value="{{ $role->name }}"
                            class="w-4 h-4 text-indigo-600 border-gray-300 role-radio"
                            {{ $usuario->hasRole($role->name) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="p-3 text-xs text-gray-500 border-t bg-gray-50">
                    ⚡ El rol define los permisos base. Los cambios aquí afectan todos los permisos heredados.
                </div>
            </div>

            {{-- Permisos directos (adicionales) --}}
            <div class="overflow-hidden bg-white border shadow-sm lg:col-span-2 rounded-2xl">
                <div class="p-4 border-b bg-gray-50">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔐</span>
                            <h3 class="font-semibold text-gray-800">Permisos adicionales (directos)</h3>
                            <span class="px-2 py-1 text-xs text-gray-400 bg-white rounded-full shadow-sm" id="totalPermisosSpan">
                                {{ $permisosAgrupados->flatten()->count() }} permisos disponibles
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="marcarTodos" class="px-3 py-1.5 text-xs bg-green-500 text-white rounded-lg hover:bg-green-600">✓ Todos</button>
                            <button type="button" id="desmarcarTodos" class="px-3 py-1.5 text-xs bg-red-500 text-white rounded-lg hover:bg-red-600">✕ Ninguno</button>
                            <button type="button" id="cargarPermisosRol" class="px-3 py-1.5 text-xs bg-indigo-500 text-white rounded-lg hover:bg-indigo-600">🔄 Cargar permisos del rol</button>
                        </div>
                    </div>
                </div>

                {{-- Buscador --}}
                <div class="p-4 bg-white border-b">
                    <div class="relative">
                        <svg class="absolute w-4 h-4 text-gray-400 left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" id="buscarPermiso" placeholder="Buscar permiso..." class="w-full py-2 pr-4 text-sm border rounded-lg pl-9 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Listado de permisos por módulo --}}
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
                                        <input type="checkbox" class="modulo-select-all w-3.5 h-3.5 rounded border-gray-300">
                                        <span class="ml-1">Seleccionar todos</span>
                                    </label>
                                    <span class="text-xs text-gray-400 permisos-count">{{ $items->count() }} permisos</span>
                                    <button type="button" class="text-gray-400 modulo-toggle hover:text-gray-600">
                                        <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-3 modulo-content md:grid-cols-3 lg:grid-cols-4">
                                @foreach($items as $permiso)
                                @php
                                    $checked = in_array($permiso->name, $permisosDirectos);
                                @endphp
                                <label class="permiso-item flex items-center gap-2 p-1.5 rounded-lg hover:bg-indigo-50 cursor-pointer transition" data-permiso="{{ strtolower($permiso->name) }}">
                                    <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}"
                                        class="permiso-check w-3.5 h-3.5 text-indigo-600 rounded border-gray-300"
                                        {{ $checked ? 'checked' : '' }}>
                                    <span class="text-xs text-gray-600">
                                        @php
                                            $nombre = str_replace('_', ' ', $permiso->name);
                                            $partes = explode(' ', $nombre);
                                            $accion = $partes[0];
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
                        <span id="seleccionadosCount">0</span> de <span id="totalPermisos">{{ $permisosAgrupados->flatten()->count() }}</span> permisos seleccionados como DIRECTOS
                    </span>
                </div>
            </div>
        </div>

        {{-- Bloque de permisos heredados del rol (solo lectura) --}}
        @if($rolActual)
        <div class="p-4 mt-6 border border-blue-200 bg-blue-50 rounded-xl">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">🔽</span>
                <h4 class="font-semibold text-blue-800">Permisos heredados del rol "{{ $rolActual->name }}"</h4>
            </div>
            <div class="flex flex-wrap gap-2">
                @forelse($permisosDelRol as $permiso)
                    <span class="px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded-full">{{ str_replace('_', ' ', $permiso) }}</span>
                @empty
                    <span class="text-sm text-blue-600">Este rol no tiene permisos específicos asignados.</span>
                @endforelse
            </div>
            <p class="mt-2 text-xs text-blue-600">⚡ Estos permisos vienen del rol y no se pueden quitar aquí. Para modificarlos, edita el rol directamente.</p>
        </div>
        @endif

        {{-- Botones --}}
        <div class="flex flex-col items-center justify-between gap-4 mt-6 sm:flex-row">
            <a href="{{ route('usuarios.index') }}" class="w-full sm:w-auto text-center px-6 py-2.5 border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50">← Volver a usuarios</a>
            <button type="submit" class="w-full sm:w-auto px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 shadow-md">💾 Guardar cambios</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos
    const marcarTodosBtn = document.getElementById('marcarTodos');
    const desmarcarTodosBtn = document.getElementById('desmarcarTodos');
    const buscarInput = document.getElementById('buscarPermiso');
    const seleccionadosSpan = document.getElementById('seleccionadosCount');
    const roleRadios = document.querySelectorAll('.role-radio');

    // Función actualizar contador
    function actualizarContador() {
        const checks = document.querySelectorAll('input[name="permisos[]"]');
        const seleccionados = Array.from(checks).filter(cb => cb.checked).length;
        if (seleccionadosSpan) seleccionadosSpan.textContent = seleccionados;
    }

    // Función actualizar select-all por módulo
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

    // Actualizar checkbox "Seleccionar todos" cuando cambian los permisos individuales
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
                if (term === '') {
                    item.style.display = '';
                } else {
                    item.style.display = texto.includes(term) ? '' : 'none';
                }
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

    // Cargar permisos del rol seleccionado mediante Axios
    async function cargarPermisosPorRol(roleName) {
        if (!roleName) return;
        
        Swal.fire({
            title: 'Cargando permisos...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        try {
            const response = await axios.get(`/roles/${roleName}/permisos`);
            const permisosDelRol = response.data.permisos;
            
            // Marcar/desmarcar los checkboxes según los permisos del rol
            const allChecks = document.querySelectorAll('input[name="permisos[]"]');
            allChecks.forEach(checkbox => {
                checkbox.checked = permisosDelRol.includes(checkbox.value);
            });
            
            // Actualizar contador y select-all por módulo
            actualizarContador();
            actualizarSelectAllModulos();
            
            Swal.close();
            
            // Opcional: mostrar notificación de éxito
            Swal.fire({
                icon: 'success',
                title: 'Permisos cargados',
                text: `Se han cargado los permisos del rol "${roleName}"`,
                timer: 1500,
                showConfirmButton: false
            });
        } catch (error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'No se pudieron cargar los permisos'
            });
        }
    }

    // Escuchar cambios en los radios de rol
    if (roleRadios.length) {
        roleRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const roleName = this.value;
                    cargarPermisosPorRol(roleName);
                }
            });
        });
    }

    // Inicializar estados de checkboxes "Seleccionar todos" y contador
    actualizarSelectAllModulos();
    actualizarContador();
});
</script>

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