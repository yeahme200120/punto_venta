@extends('layouts.app')

@section('title', 'Rol: ' . $role->name)
@section('page-title', $role->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="flex items-center justify-between mb-6 pb-6 border-b">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">{{ $role->name }}</h2>
                <p class="text-gray-500">{{ $role->users->count() }} usuarios con este rol</p>
            </div>
            @can('editar_roles')
            <a href="{{ route('roles.edit', $role) }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 text-sm">✏️ Editar</a>
            @endcan
        </div>

        <h3 class="font-bold text-lg mb-4">Permisos ({{ $role->permissions->count() }})</h3>
        <div class="space-y-4">
            @forelse($permisosAgrupados as $modulo => $items)
            <div class="border rounded-xl p-4">
                <h4 class="text-sm font-bold uppercase text-indigo-600 mb-2">{{ $modulo }}</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($items as $permiso)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">{{ str_replace('_', ' ', $permiso->name) }}</span>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-gray-400">Sin permisos asignados.</p>
            @endforelse
        </div>

        <a href="{{ route('roles.index') }}" class="inline-block mt-6 px-6 py-3 border-2 rounded-xl">← Volver</a>
    </div>
</div>
@endsection