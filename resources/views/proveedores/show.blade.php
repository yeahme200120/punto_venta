@extends('layouts.app')

@section('title', 'Proveedor: ' . $proveedor->nombre)
@section('page-title', $proveedor->nombre)
{{-- En create.blade.php --}}
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('proveedores.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Proveedores
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Nuevo</span>
    </li>
@endsection
@section('content')

<div class="max-w-3xl mx-auto">
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="flex items-start justify-between pb-6 mb-6 border-b">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex items-center justify-center w-12 h-12 text-xl font-bold text-white rounded-full shadow bg-gradient-to-br from-amber-500 to-orange-500">🚚</div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">{{ $proveedor->nombre }}</h2>
                        <p class="text-gray-500">{{ $proveedor->rfc ?? 'Sin RFC' }}</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('proveedores.edit', $proveedor) }}" class="px-4 py-2 text-sm font-medium text-white transition shadow bg-amber-500 rounded-xl hover:bg-amber-600">✏️ Editar</a>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="p-4 bg-slate-50 rounded-xl">
                <p class="text-sm text-gray-500">Teléfono</p>
                <p class="font-semibold">{{ $proveedor->telefono ?? 'No registrado' }}</p>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl">
                <p class="text-sm text-gray-500">Correo</p>
                <p class="font-semibold">{{ $proveedor->correo ?? 'No registrado' }}</p>
            </div>
            <div class="col-span-2 p-4 bg-slate-50 rounded-xl">
                <p class="text-sm text-gray-500">Dirección</p>
                <p class="font-semibold">{{ $proveedor->direccion ?? 'No registrada' }}</p>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl">
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-semibold {{ $proveedor->activo ? 'text-green-600' : 'text-red-600' }}">
                    {{ $proveedor->activo ? '● Activo' : '● Inactivo' }}
                </p>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl">
                <p class="text-sm text-gray-500">Fecha de registro</p>
                <p class="font-semibold">{{ $proveedor->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <a href="{{ route('proveedores.index') }}" class="inline-flex items-center gap-2 px-6 py-3 mt-6 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
            ← Volver a proveedores
        </a>
    </div>
</div>
@endsection