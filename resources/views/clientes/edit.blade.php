@extends('layouts.app')

@section('title', 'Editar Cliente')
@section('page-title', 'Editar: ' . $cliente->nombre)

@section('content')

<div class="max-w-2xl mx-auto">

    <x-alert type="error" :message="session('error')" />

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <h4 class="text-red-700 font-semibold mb-2">⚠️ Corrige los siguientes errores:</h4>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">👤</div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $cliente->nombre }}</h2>
        </div>

        <form action="{{ route('clientes.update', $cliente) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">RFC</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $cliente->rfc) }}"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Correo</label>
                        <input type="email" name="correo" value="{{ old('correo', $cliente->correo) }}"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('correo') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                    <textarea name="direccion" rows="2"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">{{ old('direccion', $cliente->direccion) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sucursal</label>
                    <select name="sucursal_id"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sin sucursal</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ $cliente->sucursal_id == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cliente *</label>
                    <select name="tipo" id="tipoCliente" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="contado" {{ $cliente->tipo == 'contado' ? 'selected' : '' }}>💵 Contado</option>
                        <option value="credito" {{ $cliente->tipo == 'credito' ? 'selected' : '' }}>📋 Crédito</option>
                    </select>
                </div>

                <div id="creditoFields" class="space-y-5 {{ $cliente->tipo == 'credito' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Límite de crédito $</label>
                            <input type="number" name="limite_credito" value="{{ old('limite_credito', $cliente->limite_credito) }}" min="0" step="0.01"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Días de crédito</label>
                            <input type="number" name="dias_credito" value="{{ old('dias_credito', $cliente->dias_credito) }}" min="0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-4 rounded-xl">
                        <input type="checkbox" name="activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ $cliente->activo ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Cliente activo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-between mt-8 pt-6 border-t">
                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar este cliente?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-6 py-3 border-2 border-red-300 text-red-600 rounded-xl hover:bg-red-50 transition font-medium">🗑️ Eliminar</button>
                </form>
                <div class="flex gap-4">
                    <a href="{{ route('clientes.index') }}"
                        class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">Cancelar</a>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg flex items-center gap-2">
                        💾 Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('tipoCliente').addEventListener('change', function() {
    const creditoFields = document.getElementById('creditoFields');
    creditoFields.classList.toggle('hidden', this.value !== 'credito');
});
</script>
@endsection