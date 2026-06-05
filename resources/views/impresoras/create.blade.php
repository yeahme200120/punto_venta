@extends('layouts.app')

@section('title', 'Nueva Impresora')
@section('page-title', 'Nueva Impresora')

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
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">🖨️</div>
            <h2 class="text-2xl font-bold text-slate-800">Registrar impresora</h2>
            <p class="text-gray-500 mt-2">Configura una nueva impresora</p>
        </div>

        <form action="{{ route('impresoras.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                        placeholder="Ej: Impresora Tickets Caja 1"
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                    <select name="tipo" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('tipo') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <option value="ticket" {{ old('tipo') == 'ticket' ? 'selected' : '' }}>🎫 Ticket</option>
                        <option value="factura" {{ old('tipo') == 'factura' ? 'selected' : '' }}>🧾 Factura</option>
                        <option value="etiqueta" {{ old('tipo') == 'etiqueta' ? 'selected' : '' }}>🏷️ Etiqueta</option>
                    </select>
                    @error('tipo') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sucursal</label>
                    <select name="sucursal_id"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas las sucursales</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Puerto</label>
                        <input type="text" name="puerto" value="{{ old('puerto') }}"
                            placeholder="Ej: USB, COM1"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dirección IP</label>
                        <input type="text" name="ip" value="{{ old('ip') }}"
                            placeholder="Ej: 192.168.1.100"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('ip') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        @error('ip') <p class="text-red-500 text-sm mt-1">⚠️ {{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('impresoras.index') }}"
                    class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">Cancelar</a>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg flex items-center gap-2">
                    💾 Registrar impresora
                </button>
            </div>
        </form>
    </div>
</div>
@endsection