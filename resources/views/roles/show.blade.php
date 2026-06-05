@extends('layouts.app')

@section('title', 'Rol: ' . $role->name)
@section('page-title', 'Detalle del rol')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-white rounded-3xl shadow-lg p-8">

        <div class="flex items-center justify-between mb-6 pb-6 border-b">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">{{ $role->name }}</h2>
                <p class="text-gray-500">{{ $role->users->count() }} usuarios con este rol</p>
            </div>
            <a href="{{ route('roles.edit', $role) }}"
                class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-medium shadow text-sm">
                ✏️ Editar
            </a>
        </div>

        <h3 class="font-bold text-lg text-slate-800 mb-4">Permisos ({{ $role->permissions->count() }})</h3>

        <div class="space-y-4">
            @forelse($permisosAgrupados as $modulo => $items)
            <div class="border border-slate-100 rounded-xl p-4">
                <h4 class="text-sm font-bold uppercase text-indigo-600 mb-2">{{ strtoupper($modulo) }}</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($items as $permiso)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                            {{ str_replace('_', ' ', $permiso->name) }}
                        </span>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-gray-400">Este rol no tiene permisos asignados.</p>
            @endforelse
        </div>

        <div class="mt-6">
            <a href="{{ route('roles.index') }}"
                class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 transition font-medium inline-block">
                ← Volver a roles
            </a>
        </div>
    </div>
</div>

@endsection