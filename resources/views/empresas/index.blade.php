@extends('layouts.app')

@section('title', 'Empresas')
@section('page-title', 'Empresas')

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <span class="text-sm text-gray-400">
        Mostrando {{ $empresas->count() }} de {{ $empresas->total() }} empresas
    </span>
    <a href="{{ route('empresas.export') }}" 
        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-medium shadow flex items-center gap-2">
        📥 Exportar Excel
    </a>
</div>

<div class="bg-white rounded-3xl shadow-lg overflow-hidden">
    <div class="p-6 border-b flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Todas las empresas</h2>
            <p class="text-sm text-gray-500 mt-1">Gestión global de empresas</p>
        </div>
        <a href="{{ route('empresas.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl hover:from-indigo-700 hover:to-cyan-600 transition font-medium shadow text-sm">
            + Nueva empresa
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Empresa</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">RFC</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Licencia</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Sucursales</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Usuarios</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Vence</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($empresas as $empresa)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-semibold text-slate-800">{{ $empresa->nombre }}</td>
                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $empresa->rfc }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">{{ $empresa->licencia->nombre }}</span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm">{{ $empresa->sucursales->count() }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $empresa->usuarios->count() }}</td>
                    <td class="px-6 py-4 text-center text-sm {{ $empresa->fecha_fin < now() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                        {{ $empresa->fecha_fin->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($empresa->activo && $empresa->fecha_fin >= now())
                            <span class="text-green-600 text-sm">● Activo</span>
                        @else
                            <span class="text-red-600 text-sm">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('empresa.cambiar', $empresa) }}"
                                class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs hover:bg-green-200 transition" title="Entrar">🚪</a>
                            <a href="{{ route('empresas.show', $empresa) }}" class="p-2 hover:text-indigo-600" title="Ver">👁️</a>
                            <a href="{{ route('empresas.edit', $empresa) }}" class="p-2 hover:text-amber-600" title="Editar">✏️</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">No hay empresas registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $empresas->links() }}
    </div>
</div>
@endsection