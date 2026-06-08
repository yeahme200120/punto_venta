@extends('layouts.app')

@section('title', 'Cajas')
@section('page-title', 'Cajas')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Cajas</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-400">Mostrando {{ $cajas->count() }} de {{ $cajas->total() }} cajas</span>
    </div>
    <div class="flex gap-2">
        @can('ver_dashboard_caja')
        <a href="{{ route('dashboard.caja') }}" 
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-emerald-600 to-teal-500 rounded-xl hover:from-emerald-700 hover:to-teal-600">
            📊 Dashboard
        </a>
        @endcan
        @can('crear_caja')
        <a href="{{ route('cajas.cajas.create') }}" 
            class="px-4 py-2 text-sm font-medium text-white transition shadow bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
            + Nueva caja
        </a>
        @endcan
    </div>
</div>

<div class="overflow-hidden bg-white shadow-lg rounded-3xl">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold text-slate-800">Lista de cajas</h2>
        <p class="mt-1 text-sm text-gray-500">Administra las cajas de la sucursal</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Sucursal</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Saldo Actual</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Múltiple</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($cajas as $caja)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm font-medium text-indigo-600">{{ $caja->codigo }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <span class="font-medium text-slate-800">{{ $caja->nombre }}</span>
                            @if($caja->descripcion)
                            <p class="text-xs text-gray-400">{{ Str::limit($caja->descripcion, 40) }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $caja->sucursal->nombre ?? '—' }}</td>
                    <td class="px-6 py-4 font-semibold text-center text-green-600">
                        ${{ number_format($caja->saldo_actual, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($caja->activo)
                            <span class="text-sm text-green-600">● Activo</span>
                        @else
                            <span class="text-sm text-red-600">● Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($caja->permite_multiple)
                            <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">✅ Múltiple</span>
                        @else
                            <span class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">🔒 Una sola</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            @can('editar_caja')
                            <a href="{{ route('cajas.cajas.edit', $caja) }}" class="p-2 text-gray-400 transition hover:text-amber-600" title="Editar">
                                ✏️
                            </a>
                            @endcan
                            @if($caja->aperturaActual && auth()->user()->can('ver_reporte_caja_diario'))
                            <a href="{{ route('cajas.reporte.dia', ['aperturaId' => $caja->aperturaActual->id]) }}" class="p-2 text-gray-400 transition hover:text-indigo-600" title="Reporte">
                                📊
                            </a>
                            @endif
                        </div>
                    </td>
                 </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No hay cajas registradas
                        @can('crear_caja')
                        <div class="mt-2">
                            <a href="{{ route('cajas.cajas.create') }}" class="text-indigo-600 hover:text-indigo-800">+ Crear primera caja</a>
                        </div>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t">
        {{ $cajas->links() }}
    </div>
</div>
@endsection