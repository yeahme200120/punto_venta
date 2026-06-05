@extends('layouts.app')

@section('title', 'Nuevo Insumo')
@section('page-title', 'Nuevo Insumo')

@section('content')

    <div class="max-w-2xl mx-auto">

        <x-alert type="error" :message="session('error')" />
        @if($errors->any())
            <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
                <h4 class="mb-2 font-semibold text-red-700">⚠️ Corrige los siguientes errores:</h4>
                <ul class="space-y-1 text-sm text-red-600 list-disc list-inside">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="p-8 bg-white shadow-lg rounded-3xl">
            <div class="mb-8 text-center">
                <div
                    class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl font-bold text-white rounded-full shadow-lg bg-gradient-to-br from-indigo-600 to-cyan-500">
                    🧱</div>
                <h2 class="text-2xl font-bold text-slate-800">Registrar insumo</h2>
            </div>

            <form action="{{ route('insumos.store') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label><input type="text"
                            name="nombre" value="{{ old('nombre') }}" required
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    </div>
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Código</label><input type="text"
                            name="codigo" value="{{ old('codigo') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Proveedor</label>
                        <select name="proveedor_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <option value="">Sin proveedor</option>
                            @foreach($proveedores as $prov) <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>{{ $prov->nombre }}</option> @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        {{-- En create/edit de insumos --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Unidad de Medida</label>
                            <select name="unidad_medida_id"
                                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">Seleccionar unidad...</option>
                                @foreach($unidadesMedida as $unidad)
                                    <option value="{{ $unidad->id }}" {{ old('unidad_medida_id', $insumo->unidad_medida_id ?? '') == $unidad->id ? 'selected' : '' }}>
                                        {{ $unidad->clave }} - {{ $unidad->nombre }} ({{ $unidad->simbolo ?? 'sin símbolo' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Costo Unitario $ *</label><input
                                type="number" name="costo_unitario" value="{{ old('costo_unitario', 0) }}" required min="0"
                                step="0.01"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Stock *</label><input type="number"
                                name="stock" value="{{ old('stock', 0) }}" required min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Stock Mínimo</label><input
                                type="number" name="stock_minimo" value="{{ old('stock_minimo', 10) }}" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Stock Máximo</label><input
                                type="number" name="stock_maximo" value="{{ old('stock_maximo', 500) }}" min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Descripción</label><textarea
                            name="descripcion" rows="2"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">{{ old('descripcion') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                    <a href="{{ route('insumos.index') }}"
                        class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit"
                        class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl">💾
                        Crear insumo</button>
                </div>
            </form>
        </div>
    </div>
@endsection