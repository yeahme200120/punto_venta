@extends('layouts.app')

@section('title', 'Respaldos')
@section('page-title', 'Respaldos de Información')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Respaldos</span></li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Tarjeta de información --}}
    <div class="p-6 bg-white border shadow-sm rounded-2xl">
        <div class="flex items-center gap-4 mb-4">
            <div class="p-3 bg-indigo-100 rounded-full">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Respaldo de datos</h2>
                <p class="text-sm text-gray-500">Empresa activa: <strong>{{ $empresa->nombre ?? 'N/A' }}</strong> (ID: {{ $empresa->id ?? 'N/A' }})</p>
            </div>
        </div>
        <p class="text-sm text-gray-600">Genera un respaldo completo de tu empresa en formato Excel (múltiples hojas) o SQL.</p>
    </div>

    {{-- Opciones de respaldo --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Respaldo Excel --}}
        <div class="p-6 transition bg-white border shadow-sm rounded-2xl hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📊</span>
                    <h3 class="text-lg font-bold">Respaldo Excel</h3>
                </div>
                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">Recomendado</span>
            </div>
            <p class="mb-4 text-sm text-gray-600">Genera un archivo Excel con múltiples hojas (clientes, productos, ventas, etc.) para respaldo o migración.</p>
            <a href="{{ route('respaldos.exportar.excel') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 text-white transition bg-green-600 rounded-xl hover:bg-green-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Descargar Excel
            </a>
        </div>

        {{-- Respaldo SQL --}}
        <div class="p-6 transition bg-white border shadow-sm rounded-2xl hover:shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl">💾</span>
                <h3 class="text-lg font-bold">Respaldo SQL</h3>
            </div>
            <p class="mb-4 text-sm text-gray-600">Genera un archivo SQL con la estructura y datos de la empresa (compatible con phpMyAdmin).</p>
            <a href="{{ route('respaldos.exportar.sql') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 text-white transition bg-indigo-600 rounded-xl hover:bg-indigo-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Descargar SQL
            </a>
        </div>
    </div>

    {{-- Importación --}}
    <div class="p-6 bg-white border shadow-sm rounded-2xl">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-3xl">📥</span>
            <h3 class="text-lg font-bold">Importar respaldo</h3>
        </div>
        <p class="mb-4 text-sm text-gray-600">Restaura los datos desde un archivo Excel generado previamente.</p>
        
        <form action="{{ route('respaldos.importar') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="block mb-1 text-sm font-medium text-gray-700">Archivo Excel (.xlsx, .xls, .csv)</label>
                <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required class="w-full px-4 py-2 border rounded-xl">
                <p class="mt-1 text-xs text-gray-400">Máx. 10MB. Solo archivos exportados desde este sistema.</p>
                @error('archivo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="flex items-center gap-2 px-6 py-2 text-white transition bg-yellow-600 rounded-xl hover:bg-yellow-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3 3m0 0l-3-3m3 3V8"></path>
                </svg>
                Importar
            </button>
        </form>
    </div>

    {{-- Advertencia --}}
    <div class="p-4 border border-yellow-200 bg-yellow-50 rounded-xl">
        <div class="flex items-center gap-2">
            <span class="text-xl">⚠️</span>
            <p class="text-sm text-yellow-800">
                <strong>Nota importante:</strong> Los respaldos contienen SOLO los datos de la empresa activa actual. 
                Al importar, los datos se combinarán con los existentes (actualizando registros con el mismo ID).
            </p>
        </div>
    </div>
</div>
@endsection