@extends('layouts.app')

@section('title', 'Permisos de ' . $usuario->name)
@section('page-title', 'Permisos: ' . $usuario->name)
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('usuarios.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Usuarios
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">{{ $usuario->name }}</span>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Permisos</span>
    </li>
@endsection

@section('content')
<div class="mx-auto max-w-7xl">
    {{-- Info usuario --}}
    <div class="p-5 mb-5 bg-white border border-gray-100 shadow-sm rounded-2xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center text-xl font-bold text-white rounded-full shadow-md w-14 h-14 bg-gradient-to-br from-indigo-600 to-cyan-500">
                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $usuario->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $usuario->email }}</p>
                    <div class="flex flex-wrap gap-3 mt-1 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                            {{ $usuario->empresa->nombre ?? 'Sin empresa' }}
                        </span>
                        @if($usuario->sucursal)
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                            {{ $usuario->sucursal->nombre }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1">
                            @if($usuario->activo)
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span> Activo
                            @else
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span> Inactivo
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                <span class="font-medium">Última actualización:</span> {{ $usuario->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <form action="{{ route('usuarios.permisos.update', $usuario) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            {{-- Roles --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div class="p-4 border-b bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🎭</span>
                            <h3 class="font-semibold text-gray-800">Roles</h3>
                        </div>
                        <span class="px-2 py-1 text-xs text-gray-400 bg-white rounded-full shadow-sm">
                            {{ $roles->count() }} roles
                        </span>
                    </div>
                </div>
                <div class="p-4 space-y-2 max-h-[400px] overflow-y-auto">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-3 p-2 transition rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500"
                            {{ $usuario->hasRole($role->name) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Permisos --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm lg:col-span-2 rounded-2xl">
                <div class="p-4 border-b bg-gray-50">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔐</span>
                            <h3 class="font-semibold text-gray-800">Permisos</h3>
                            <span class="px-2 py-1 text-xs text-gray-400 bg-white rounded-full shadow-sm">
                                {{ $permisos->flatten()->count() }} permisos
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="marcarTodos"
                                class="px-3 py-1.5 text-xs bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                ✓ Todos
                            </button>
                            <button type="button" id="desmarcarTodos"
                                class="px-3 py-1.5 text-xs bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                ✕ Ninguno
                            </button>
                            <button type="button" id="toggleSeleccion"
                                class="px-3 py-1.5 text-xs bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition">
                                ↻ Invertir
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Buscador de permisos --}}
                <div class="p-4 bg-white border-b">
                    <div class="relative">
                        <svg class="absolute w-4 h-4 text-gray-400 -translate-y-1/2 left-3 top-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" id="buscarPermiso" placeholder="Buscar permiso por nombre..." 
                            class="w-full py-2 pr-4 text-sm border rounded-lg pl-9 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Lista de permisos por módulo --}}
                <div class="p-4 max-h-[500px] overflow-y-auto">
                    <div class="space-y-4">
                        @foreach($permisos as $modulo => $items)
                        <div class="overflow-hidden border border-gray-100 modulo-card rounded-xl">
                            <div class="flex items-center justify-between p-3 cursor-pointer bg-gray-50 modulo-header">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg modulo-icon">
                                        @switch(strtolower($modulo))
                                            @case('dashboard') 📊 @break
                                            @case('empresas') 🏢 @break
                                            @case('licencias') 📜 @break
                                            @case('inventario') 📦 @break
                                            @case('compras') 🛒 @break
                                            @case('proveedores') 🚚 @break
                                            @case('ventas') 💰 @break
                                            @case('facturacion') 🧾 @break
                                            @case('clientes') 👥 @break
                                            @case('caja') 💵 @break
                                            @case('cobranza') 📋 @break
                                            @case('formaspago') 💳 @break
                                            @case('notificaciones') 🔔 @break
                                            @case('impresoras') 🖨️ @break
                                            @case('ticket') 🎫 @break
                                            @case('usuarios') 🔐 @break
                                            @case('roles') 🎭 @break
                                            @case('reportes') 📈 @break
                                            @case('respaldos') 💾 @break
                                            @default 📌
                                        @endswitch
                                    </span>
                                    <span class="text-sm font-semibold text-gray-700 uppercase">{{ str_replace('_', ' ', $modulo) }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-400 permisos-count">{{ $items->count() }} permisos</span>
                                    <button type="button" class="text-gray-400 modulo-toggle hover:text-gray-600">
                                        <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-3 modulo-content md:grid-cols-3 lg:grid-cols-4">
                                @foreach($items as $permiso)
                                <label class="permiso-item flex items-center gap-2 p-1.5 rounded-lg hover:bg-indigo-50 cursor-pointer transition" data-permiso="{{ strtolower($permiso->name) }}">
                                    <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}"
                                        class="permiso-check w-3.5 h-3.5 text-indigo-600 rounded border-gray-300 focus:ring-2 focus:ring-indigo-500"
                                        {{ $usuario->hasPermissionTo($permiso->name) ? 'checked' : '' }}>
                                    <span class="text-xs text-gray-600">
                                        @php
                                            $nombrePermiso = str_replace('_', ' ', $permiso->name);
                                            $partes = explode(' ', $nombrePermiso);
                                            $accion = $partes[0] ?? '';
                                            $resto = implode(' ', array_slice($partes, 1));
                                        @endphp
                                        <span class="font-semibold 
                                            @if($accion == 'ver') text-blue-600
                                            @elseif($accion == 'crear') text-green-600
                                            @elseif($accion == 'editar') text-amber-600
                                            @elseif($accion == 'eliminar') text-red-600
                                            @else text-gray-600
                                            @endif">
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

                {{-- Resumen de selección --}}
                <div class="flex items-center justify-between p-4 text-sm border-t bg-gray-50">
                    <span class="text-gray-500">
                        <span id="seleccionadosCount">0</span> de <span id="totalPermisos">{{ $permisos->flatten()->count() }}</span> permisos seleccionados
                    </span>
                    <span id="modulosSeleccionados" class="text-xs text-gray-400"></span>
                </div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="flex flex-col items-center justify-between gap-4 mt-6 sm:flex-row">
            <a href="{{ route('usuarios.index') }}"
                class="w-full sm:w-auto text-center px-6 py-2.5 border border-gray-300 rounded-xl text-gray-600 hover:bg-gray-50 transition font-medium">
                ← Volver a usuarios
            </a>
            <button type="submit"
                class="w-full sm:w-auto px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-md">
                💾 Guardar cambios
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    // Elementos
    const marcarTodosBtn = document.getElementById('marcarTodos');
    const desmarcarTodosBtn = document.getElementById('desmarcarTodos');
    const toggleSeleccionBtn = document.getElementById('toggleSeleccion');
    const buscarInput = document.getElementById('buscarPermiso');
    const seleccionadosSpan = document.getElementById('seleccionadosCount');
    const modulosSeleccionadosSpan = document.getElementById('modulosSeleccionados');

    // Actualizar contador
    function actualizarContador() {
        const checks = document.querySelectorAll('input[name="permisos[]"]');
        const seleccionados = Array.from(checks).filter(cb => cb.checked).length;
        seleccionadosSpan.textContent = seleccionados;
        
        // Contar módulos completos
        const modulos = document.querySelectorAll('.modulo-card');
        let modulosCompletos = 0;
        modulos.forEach(modulo => {
            const checksModulo = modulo.querySelectorAll('.permiso-check');
            const totalModulo = checksModulo.length;
            const seleccionadosModulo = Array.from(checksModulo).filter(cb => cb.checked).length;
            if (totalModulo > 0 && seleccionadosModulo === totalModulo) {
                modulosCompletos++;
            }
        });
        modulosSeleccionadosSpan.textContent = modulosCompletos > 0 ? `${modulosCompletos} módulos completos` : '';
    }

    // Marcar/Desmarcar todos
    marcarTodosBtn.addEventListener('click', () => {
        document.querySelectorAll('input[name="permisos[]"]').forEach(cb => cb.checked = true);
        actualizarContador();
    });
    desmarcarTodosBtn.addEventListener('click', () => {
        document.querySelectorAll('input[name="permisos[]"]').forEach(cb => cb.checked = false);
        actualizarContador();
    });
    toggleSeleccionBtn.addEventListener('click', () => {
        document.querySelectorAll('input[name="permisos[]"]').forEach(cb => cb.checked = !cb.checked);
        actualizarContador();
    });

    // Buscador
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
        // Ocultar módulos vacíos
        document.querySelectorAll('.modulo-card').forEach(modulo => {
            const visibles = modulo.querySelectorAll('.permiso-item[style=""]');
            modulo.style.display = visibles.length > 0 ? '' : 'none';
        });
    });

    // Toggle módulos
    document.querySelectorAll('.modulo-header').forEach(header => {
        header.addEventListener('click', (e) => {
            if (e.target.type !== 'checkbox' && !e.target.closest('.modulo-toggle')) {
                const content = header.nextElementSibling;
                const icon = header.querySelector('.modulo-toggle svg');
                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            }
        });
    });

    // Eventos de cambio en checkboxes
    document.querySelectorAll('input[name="permisos[]"]').forEach(cb => {
        cb.addEventListener('change', actualizarContador);
    });

    // Inicializar contador
    actualizarContador();

    // Mostrar todos los módulos expandidos por defecto
    document.querySelectorAll('.modulo-content').forEach(content => {
        content.classList.remove('hidden');
    });
})();
</script>

<style>
.modulo-content.hidden { display: none; }
.rotate-180 { transform: rotate(180deg); }
.modulo-header { cursor: pointer; }
.permiso-item:hover { background-color: #eef2ff; }
</style>
@endsection