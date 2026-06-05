@extends('layouts.app')

@section('title', 'Permisos de ' . $usuario->name)
@section('page-title', 'Permisos: ' . $usuario->name)

@section('content')

<div class="max-w-7xl mx-auto">

    <!-- INFO USUARIO -->
    <div class="bg-white rounded-3xl shadow-lg p-6 mb-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg">
                {{ strtoupper(substr($usuario->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">{{ $usuario->name }}</h2>
                <p class="text-gray-500">{{ $usuario->email }}</p>
                <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-500">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                        {{ $usuario->empresa->nombre ?? 'Sin empresa' }}
                    </span>
                    @if($usuario->sucursal)
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-cyan-500 rounded-full"></span>
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
    </div>

    <form action="{{ route('usuarios.permisos.update', $usuario) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ROLES -->
            <div class="bg-white rounded-3xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-800">Roles</h3>
                    <span class="text-xs text-gray-400 bg-slate-100 px-2 py-1 rounded-full">
                        {{ $roles->count() }} roles
                    </span>
                </div>

                <div class="space-y-1">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 cursor-pointer transition border border-transparent hover:border-slate-200">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                            class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                            {{ $usuario->hasRole($role->name) ? 'checked' : '' }}>
                        <span class="font-medium text-slate-700">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- PERMISOS POR MÓDULO -->
            <div class="lg:col-span-2 bg-white rounded-3xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-800">Permisos</h3>
                    <span class="text-xs text-gray-400 bg-slate-100 px-2 py-1 rounded-full">
                        {{ $permisos->flatten()->count() }} permisos
                    </span>
                </div>

                <!-- Botones de acción rápida -->
                <div class="flex flex-wrap gap-2 mb-6 pb-4 border-b border-slate-100">
                    <button type="button" id="marcarTodos"
                        class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl hover:from-green-600 hover:to-emerald-600 transition text-sm font-medium shadow">
                        ✓ Marcar todos
                    </button>

                    <button type="button" id="desmarcarTodos"
                        class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-500 text-white rounded-xl hover:from-red-600 hover:to-rose-600 transition text-sm font-medium shadow">
                        ✕ Desmarcar todos
                    </button>

                    <button type="button" id="toggleSeleccion"
                        class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:from-amber-600 hover:to-orange-600 transition text-sm font-medium shadow">
                        ↻ Invertir selección
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach($permisos as $modulo => $items)
                    <div class="border border-slate-100 rounded-2xl p-4 hover:border-slate-200 transition">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold uppercase text-indigo-600 flex items-center gap-2">
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
                                {{ strtoupper($modulo) }}
                            </h4>
                            <button type="button" class="marcarModulo text-xs text-indigo-500 hover:text-indigo-700 font-medium"
                                data-modulo="{{ $modulo }}">
                                Seleccionar todos
                            </button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-1">
                            @foreach($items as $permiso)
                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-indigo-50 cursor-pointer text-sm transition">
                                <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}"
                                    class="permiso-check w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                                    data-modulo="{{ $modulo }}"
                                    {{ $usuario->hasPermissionTo($permiso->name) ? 'checked' : '' }}>
                                <span class="text-slate-600 text-xs">
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
                                        @else text-slate-600
                                        @endif">
                                        {{ $accion }}
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

        </div>

        <!-- BOTONES INFERIORES -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-6">
            <a href="{{ route('usuarios.index') }}"
                class="w-full sm:w-auto text-center px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition font-medium">
                ← Volver a usuarios
            </a>

            <button type="submit"
                class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg">
                💾 Guardar cambios
            </button>
        </div>

    </form>

</div>

<script>
(function() {
    // Marcar todos
    document.getElementById('marcarTodos').addEventListener('click', function() {
        document.querySelectorAll('input[name="permisos[]"]').forEach(function(cb) {
            cb.checked = true;
        });
        actualizarBotonesModulo();
    });

    // Desmarcar todos
    document.getElementById('desmarcarTodos').addEventListener('click', function() {
        document.querySelectorAll('input[name="permisos[]"]').forEach(function(cb) {
            cb.checked = false;
        });
        actualizarBotonesModulo();
    });

    // Invertir selección
    document.getElementById('toggleSeleccion').addEventListener('click', function() {
        document.querySelectorAll('input[name="permisos[]"]').forEach(function(cb) {
            cb.checked = !cb.checked;
        });
        actualizarBotonesModulo();
    });

    // Marcar por módulo
    function actualizarBotonesModulo() {
        document.querySelectorAll('.marcarModulo').forEach(function(btn) {
            var modulo = btn.getAttribute('data-modulo');
            var checks = document.querySelectorAll('.permiso-check[data-modulo="' + modulo + '"]');
            var todosMarcados = Array.from(checks).every(function(cb) { return cb.checked; });
            btn.textContent = todosMarcados ? 'Deseleccionar todos' : 'Seleccionar todos';
        });
    }

    document.querySelectorAll('.marcarModulo').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modulo = this.getAttribute('data-modulo');
            var checks = document.querySelectorAll('.permiso-check[data-modulo="' + modulo + '"]');
            var todosMarcados = Array.from(checks).every(function(cb) { return cb.checked; });

            checks.forEach(function(cb) {
                cb.checked = !todosMarcados;
            });
            this.textContent = todosMarcados ? 'Seleccionar todos' : 'Deseleccionar todos';
        });
    });

    // Inicializar estado de botones
    actualizarBotonesModulo();
})();
</script>

@endsection