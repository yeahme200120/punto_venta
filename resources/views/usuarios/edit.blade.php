@extends('layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar: ' . $usuario->name)

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- ALERTAS --}}
    <x-alert type="error" :message="session('error')" />
    <x-alert type="success" :message="session('success')" />

    {{-- ERRORES DE VALIDACIÓN --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <h4 class="text-red-700 font-semibold mb-2 flex items-center gap-2">
            ⚠️ Corrige los siguientes errores:
        </h4>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-lg p-8">

        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">
                {{ strtoupper(substr($usuario->name, 0, 1)) }}
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $usuario->name }}</h2>
            <p class="text-gray-500 mt-2">{{ $usuario->email }}</p>
        </div>

        <form action="{{ route('usuarios.update', $usuario) }}" method="POST" id="formEditUser">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- SELECTOR DE EMPRESA (SOLO SUPER ADMIN) --}}
                @if(auth()->user()->hasRole('Super Admin') && $empresas->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Empresa *
                        <span class="text-xs text-amber-600 font-normal">(Solo Super Admin)</span>
                    </label>
                    <select name="empresa_id" id="selectEmpresa" required
                        class="w-full border-2 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('empresa_id') ? 'border-red-500 bg-red-50' : 'border-indigo-200 bg-indigo-50' }}">
                        <option value="">Seleccionar empresa...</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}" {{ old('empresa_id', $usuario->empresa_id) == $emp->id ? 'selected' : '' }}
                                data-tiene-sucursales="{{ $emp->sucursales->where('activo', true)->count() }}">
                                {{ $emp->nombre }} ({{ $emp->rfc }})
                            </option>
                        @endforeach
                    </select>
                    @error('empresa_id')
                        <p class="text-red-500 text-sm mt-1 flex items-center gap-1">⚠️ {{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('name') <p class="text-red-500 text-sm mt-1 flex items-center gap-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('email') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('email') <p class="text-red-500 text-sm mt-1 flex items-center gap-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nueva contraseña
                        <span class="text-xs text-gray-400 font-normal">(dejar vacío para mantener la actual)</span>
                    </label>
                    <input type="password" name="password"
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('password') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('password') <p class="text-red-500 text-sm mt-1 flex items-center gap-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Confirmar Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation"
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('password_confirmation') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                </div>

                <!-- Sucursal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sucursal
                        <span id="sucursalCount" class="text-xs text-gray-400 font-normal"></span>
                    </label>
                    <select name="sucursal_id" id="selectSucursal"
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('sucursal_id') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <option value="">Sin sucursal</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" data-empresa="{{ $sucursal->empresa_id }}"
                                {{ old('sucursal_id', $usuario->sucursal_id) == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('sucursal_id') <p class="text-red-500 text-sm mt-1 flex items-center gap-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Activo -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-4 rounded-xl">
                        <input type="checkbox" name="activo" value="1"
                            class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                            {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Usuario activo</span>
                    </label>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($roles as $role)
                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 cursor-pointer transition">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                                {{ $usuario->hasRole($role->name) ? 'checked' : '' }}>
                            <span class="text-sm">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-between gap-4 mt-8 pt-6 border-t">
                <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar este usuario definitivamente?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-6 py-3 border-2 border-red-300 text-red-600 rounded-xl hover:bg-red-50 hover:border-red-400 transition font-medium">
                        🗑️ Eliminar
                    </button>
                </form>

                <div class="flex gap-4">
                    <a href="{{ route('usuarios.index') }}"
                        class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg flex items-center gap-2">
                        💾 Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(auth()->user()->hasRole('Super Admin'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectEmpresa = document.getElementById('selectEmpresa');
    const selectSucursal = document.getElementById('selectSucursal');
    const sucursalCount = document.getElementById('sucursalCount');
    const todasSucursales = Array.from(selectSucursal.options);

    function filtrarSucursales(empresaId) {
        const selectedValue = selectSucursal.value;
        selectSucursal.innerHTML = '<option value="">Sin sucursal</option>';
        
        if (!empresaId) {
            sucursalCount.textContent = '';
            return;
        }

        let count = 0;
        todasSucursales.forEach(opt => {
            if (opt.dataset.empresa == empresaId && opt.value) {
                const clone = opt.cloneNode(true);
                if (opt.value == selectedValue) clone.selected = true;
                selectSucursal.appendChild(clone);
                count++;
            }
        });

        sucursalCount.textContent = count > 0 
            ? `(${count} disponible${count > 1 ? 's' : ''})` 
            : '(Sin sucursales)';
    }

    if (selectEmpresa) {
        selectEmpresa.addEventListener('change', function() {
            filtrarSucursales(this.value);
        });

        if (selectEmpresa.value) {
            filtrarSucursales(selectEmpresa.value);
        }
    }
});
</script>
@endif

@endsection