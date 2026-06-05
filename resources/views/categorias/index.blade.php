@extends('layouts.app')

@section('title', 'Categorías')
@section('page-title', 'Categorías')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if(isset($empresaActiva))
        <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">🏢 {{ $empresaActiva->nombre }}</span>
        @endif
        <span class="text-sm text-gray-400">Mostrando {{ $categorias->count() }} de {{ $categorias->total() }} categorías</span>
    </div>
    @if(auth()->user()->hasRole(['Super Admin', 'Administrador']))
    <a href="{{ route('categorias.export') }}" 
        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-medium shadow flex items-center gap-2">
        📥 Exportar Excel
    </a>
    @endif
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Categorías de productos</h2>
            <p class="text-sm text-gray-500 mt-1">Organiza tus productos por categorías</p>
        </div>
        <a href="{{ route('categorias.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nueva categoría
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Nombre</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Descripción</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Productos</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categorias as $categoria)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-slate-800">{{ $categoria->nombre }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($categoria->descripcion, 50) ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">{{ $categoria->productos_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($categoria->activo)
                            <span class="text-green-600 text-sm">● Activo</span>
                        @else
                            <span class="text-red-600 text-sm">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('categorias.show', $categoria) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('categorias.edit', $categoria) }}" class="p-2 text-gray-400 hover:text-amber-600" title="Editar">✏️</a>
                            @if($categoria->productos_count == 0)
                            <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="inline"
                                onsubmit="return confirm('¿Eliminar esta categoría?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600">🗑️</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">No hay categorías registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">{{ $categorias->links() }}</div>
</div>
@endsection