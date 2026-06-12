@extends('layouts.app')

@section('title', 'Editar Categoría')
@section('page-title', 'Editar: ' . $categoria->nombre)

@section('content')

<div class="max-w-2xl mx-auto">

    <x-alert type="error" :message="session('error')" />
    <x-alert type="success" :message="session('success')" />
    
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
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-600 to-cyan-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">📋</div>
            <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $categoria->nombre }}</h2>
            <p class="text-gray-500 mt-2">Modifica los datos de la categoría</p>
        </div>

        <form action="{{ route('categorias.update', $categoria) }}" method="POST">
            @csrf 
            @method('PUT')
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $categoria->nombre) }}" required
                        class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                    @error('nombre') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="3"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                </div>
                
                {{-- ✅ Checkbox con input hidden para enviar 0 cuando está desmarcado --}}
                <div>
                    <input type="hidden" name="activo" value="0">
                    <label class="flex items-center gap-3 cursor-pointer bg-slate-50 p-4 rounded-xl">
                        <input type="checkbox" name="activo" value="1" 
                               class="w-5 h-5 text-indigo-600 rounded" 
                               {{ old('activo', $categoria->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Categoría activa</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('categorias.index') }}" 
                   class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium">
                    Cancelar
                </a>
                @can('editar_categorias')
                <button type="submit" 
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-semibold shadow-lg">
                    💾 Guardar cambios
                </button>
                @endcan
            </div>
        </form>
    </div>
</div>
@endsection