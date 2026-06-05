@extends('layouts.app')

@section('title', 'Nuevo Rol')
@section('page-title', 'Nuevo Rol')

@section('content')

<div class="max-w-4xl mx-auto">

    <x-alert type="error" :message="session('error')" />

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <h4 class="text-red-700 font-semibold mb-2 flex items-center gap-2">⚠️ Corrige los siguientes errores:</h4>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">+</div>
            <h2 class="text-2xl font-bold text-slate-800">Crear nuevo rol</h2>
            <p class="text-gray-500 mt-2">Define el nombre y permisos del rol</p>
        </div>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del rol *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    placeholder="Ej: Cajero, Supervisor, Gerente..."
                    class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                    {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                @error('name') <p class="text-red-500 text-sm mt-1 flex items-center gap-1">⚠️ {{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <h3 class="font-bold text-lg text-slate-800 mb-4">Permisos</h3>
                <div class="flex flex-wrap gap-2 mb-4 pb-4 border-b">
                    <button type="button" onclick="marcarTodos()"
                        class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl text-sm font-medium shadow hover:from-green-600 hover:to-emerald-600 transition">
                        ✓ Marcar todos
                    </button>
                    <button type="button" onclick="desmarcarTodos()"
                        class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-500 text-white rounded-xl text-sm font-medium shadow hover:from-red-600 hover:to-rose-600 transition">
                        ✕ Desmarcar todos
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach($permisos as $modulo => $items)
                    <div class="border border-slate-100 rounded-xl p-4 hover:border-slate-200 transition">
                        <h4 class="text-sm font-bold uppercase text-indigo-600 mb-2">
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
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-1">
                            @foreach($items as $permiso)
                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-indigo-50 cursor-pointer text-sm transition">
                                <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0"
                                    {{ in_array($permiso->name, old('permisos', [])) ? 'checked' : '' }}>
                                <span class="text-slate-600 text-xs">{{ str_replace('_', ' ', $permiso->name) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('roles.index') }}"
                    class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg flex items-center gap-2">
                    💾 Crear rol
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function marcarTodos() { document.querySelectorAll('input[name="permisos[]"]').forEach(cb => cb.checked = true); }
function desmarcarTodos() { document.querySelectorAll('input[name="permisos[]"]').forEach(cb => cb.checked = false); }
</script>

@endsection