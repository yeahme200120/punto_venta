@extends('layouts.app')

@section('title', 'Nueva Sucursal')
@section('page-title', 'Nueva Sucursal')

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
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">📍</div>
            <h2 class="text-2xl font-bold text-slate-800">Registrar sucursal</h2>
            <p class="text-gray-500 mt-2">{{ $empresa->nombre }}</p>
        </div>

        <form action="{{ route('sucursales.store') }}" method="POST">
            @csrf

            {{-- Selector de empresa para Super Admin --}}
            @if(auth()->user()->hasRole('Super Admin') && isset($empresas) && $empresas->count() > 0)
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Empresa *</label>
                <select name="empresa_id" required
                    class="w-full border-2 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('empresa_id') ? 'border-red-500 bg-red-50' : 'border-indigo-200 bg-indigo-50' }}">
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ ($empresa->id ?? '') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nombre }} ({{ $emp->rfc }})
                        </option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
            @endif

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                        placeholder="Ej: Sucursal Norte, Matriz..."
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                        placeholder="Ej: Av. Principal #123"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        placeholder="Ej: 555-123-4567"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('empresas.show', $empresa->id) }}"
                    class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">Cancelar</a>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg flex items-center gap-2">
                    💾 Crear sucursal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection