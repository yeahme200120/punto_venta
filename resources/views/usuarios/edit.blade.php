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
    <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
        <h4 class="flex items-center gap-2 mb-2 font-semibold text-red-700">
            ⚠️ Corrige los siguientes errores:
        </h4>
        <ul class="space-y-1 text-sm text-red-600 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="p-8 bg-white shadow-lg rounded-3xl">

        <div class="mb-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">
                {{ strtoupper(substr($usuario->name, 0, 1)) }}
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $usuario->name }}</h2>
            <p class="mt-2 text-gray-500">{{ $usuario->email }}</p>
        </div>

        <form action="{{ route('usuarios.update', $usuario) }}" method="POST" id="formEditUser">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- SELECTOR DE EMPRESA (SOLO SUPER ADMIN) --}}
                @if(auth()->user()->hasRole('Super Admin') && $empresas->count() > 0)
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Empresa *
                        <span class="text-xs font-normal text-amber-600">(Solo Super Admin)</span>
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
                        <p class="flex items-center gap-1 mt-1 text-sm text-red-500">⚠️ {{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Nombre -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('name') <p class="flex items-center gap-1 mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('email') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('email') <p class="flex items-center gap-1 mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Nueva contraseña
                        <span class="text-xs font-normal text-gray-400">(dejar vacío para mantener la actual)</span>
                    </label>
                    <input type="password" name="password"
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('password') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('password') <p class="flex items-center gap-1 mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Confirmar Password -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation"
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        {{ $errors->has('password_confirmation') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                </div>

                <!-- Sucursal -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Sucursal
                        <span id="sucursalCount" class="text-xs font-normal text-gray-400"></span>
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
                    @error('sucursal_id') <p class="flex items-center gap-1 mt-1 text-sm text-red-500">⚠️ {{ $message }}</p> @enderror
                </div>

                <!-- Activo -->
                <div>
                    <label class="flex items-center gap-3 p-4 cursor-pointer bg-slate-50 rounded-xl">
                        <input type="checkbox" name="activo" value="1"
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Usuario activo</span>
                    </label>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Roles</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($roles as $role)
                        <label class="flex items-center gap-2 p-2 transition rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                {{ $usuario->hasRole($role->name) ? 'checked' : '' }}>
                            <span class="text-sm">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-between gap-4 pt-6 mt-8 border-t">

                <div class="flex gap-4">
                    <a href="{{ route('usuarios.index') }}"
                        class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="flex items-center gap-2 px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
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