@extends('layouts.app')

@section('title', 'Nueva Unidad')
@section('page-title', 'Nueva Unidad de Medida')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-alert type="error" :message="session('error')" />
    @if($errors->any())
    <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
        <ul class="list-disc list-inside text-sm text-red-600">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
    @endif

    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 flex items-center justify-center text-white text-2xl">📏</div>
            <h2 class="text-2xl font-bold">Registrar unidad</h2>
        </div>
        <form action="{{ route('unidades-medida.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-medium">Tipo *</label>
                    <select name="tipo" required class="w-full border rounded-xl px-4 py-3">
                        <option value="">Seleccionar...</option>
                        @foreach(['Múltiplos / Fracciones / Decimales','Unidades de venta','Unidades específicas de la industria','Mecánica','Tiempo y Espacio','Unidades de empaque','Diversos','Números enteros / Números / Ratios'] as $tipo)
                            <option value="{{ $tipo }}" {{ old('tipo')==$tipo?'selected':'' }}>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block mb-2 text-sm font-medium">Clave *</label><input type="text" name="clave" value="{{ old('clave') }}" required class="w-full border rounded-xl px-4 py-3"></div>
                <div><label class="block mb-2 text-sm font-medium">Nombre *</label><input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full border rounded-xl px-4 py-3"></div>
                <div><label class="block mb-2 text-sm font-medium">Símbolo</label><input type="text" name="simbolo" value="{{ old('simbolo') }}" class="w-full border rounded-xl px-4 py-3"></div>
                <div><label class="block mb-2 text-sm font-medium">Descripción</label><textarea name="descripcion" rows="3" class="w-full border rounded-xl px-4 py-3">{{ old('descripcion') }}</textarea></div>
            </div>
            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('unidades-medida.index') }}" class="px-6 py-3 border-2 rounded-xl">Cancelar</a>
                @can('crear_unidades_medida')
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl">💾 Crear</button>
                @endcan
            </div>
        </form>
    </div>
</div>
@endsection