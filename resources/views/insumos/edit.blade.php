@extends('layouts.app')

@section('title', 'Editar Insumo')
@section('page-title', 'Editar: ' . $insumo->nombre)

@section('content')

    <div class="max-w-2xl mx-auto">

        <x-alert type="error" :message="session('error')" />

        <div class="p-8 bg-white shadow-lg rounded-3xl">
            <form action="{{ route('insumos.update', $insumo) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-5">
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Nombre *</label><input type="text"
                            name="nombre" value="{{ old('nombre', $insumo->nombre) }}" required
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"></div>
                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Proveedor</label>
                        <select name="proveedor_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                            <option value="">Sin proveedor</option>
                            @foreach($proveedores as $prov) <option value="{{ $prov->id }}" {{ $insumo->proveedor_id == $prov->id ? 'selected' : '' }}>{{ $prov->nombre }}</option>
                            @endforeach
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
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Costo Unitario $</label><input
                                type="number" name="costo_unitario"
                                value="{{ old('costo_unitario', $insumo->costo_unitario) }}" step="0.01"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Stock</label><input type="number"
                                name="stock" value="{{ old('stock', $insumo->stock) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"></div>
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Stock Mínimo</label><input
                                type="number" name="stock_minimo" value="{{ old('stock_minimo', $insumo->stock_minimo) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"></div>
                        <div><label class="block mb-2 text-sm font-medium text-gray-700">Stock Máximo</label><input
                                type="number" name="stock_maximo" value="{{ old('stock_maximo', $insumo->stock_maximo) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl"></div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="activo" value="1"
                            class="w-5 h-5 text-indigo-600 rounded" {{ $insumo->activo ? 'checked' : '' }}><span
                            class="text-sm">Activo</span></label>
                </div>
                <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                    <a href="{{ route('insumos.index') }}"
                        class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit"
                        class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl">💾
                        Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection