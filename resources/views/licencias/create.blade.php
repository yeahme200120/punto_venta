@extends('layouts.app')

@section('title', 'Nueva Licencia')
@section('page-title', 'Nueva Licencia')

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
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">📜</div>
            <h2 class="text-2xl font-bold text-slate-800">Crear nueva licencia</h2>
            <p class="text-gray-500 mt-2">Define el plan y sus límites</p>
        </div>

        <form action="{{ route('licencias.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                        placeholder="Ej: Plan Básico, Plan Premium..."
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Días *</label>
                        <input type="number" name="dias" value="{{ old('dias', 30) }}" required min="1"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('dias') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('dias') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Precio $ *</label>
                        <input type="number" name="precio" value="{{ old('precio', 0) }}" required min="0" step="0.01"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('precio') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('precio') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Usuarios *</label>
                        <input type="number" name="max_usuarios" value="{{ old('max_usuarios', 1) }}" required min="1"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('max_usuarios') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('max_usuarios') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Sucursales *</label>
                        <input type="number" name="max_sucursales" value="{{ old('max_sucursales', 0) }}" required min="0"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('max_sucursales') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('max_sucursales') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('licencias.index') }}"
                    class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">Cancelar</a>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg flex items-center gap-2">
                    💾 Crear licencia
                </button>
            </div>
        </form>
    </div>
</div>
@endsection