@extends('layouts.app')

@section('title', 'Contraseñas Maestras')
@section('page-title', 'Contraseñas Maestras')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
            Dashboard
        </a>
    </li>
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <span class="font-medium text-gray-700">Contraseñas Maestras</span>
    </li>
@endsection

@section('content')

<x-alert type="success" :message="session('success')" />
<x-alert type="error" :message="session('error')" />

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Información --}}
    <div class="p-4 border border-blue-200 bg-blue-50 rounded-2xl">
        <div class="flex gap-3">
            <div class="text-2xl">🔐</div>
            <div>
                <h3 class="font-semibold text-blue-800">¿Qué es una contraseña maestra?</h3>
                <p class="mt-1 text-sm text-blue-700">
                    La contraseña maestra se utiliza para autorizar operaciones delicadas como transferencias entre cajas 
                    o movimientos que requieren autorización de un administrador. Solo tú puedes crear y gestionar tus contraseñas maestras.
                </p>
            </div>
        </div>
    </div>

    {{-- Formulario para crear nueva contraseña --}}
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <h2 class="mb-6 text-xl font-bold text-slate-800">Crear nueva contraseña maestra</h2>
        
        <form action="{{ route('perfil.contraseñas.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Tipo de contraseña *</label>
                <select name="tipo" required class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <option value="admin">👤 Administrador</option>
                    @if(auth()->user()->hasRole('Super Admin'))
                    <option value="super_admin">⭐ Super Administrador</option>
                    @endif
                </select>
                <p class="mt-1 text-xs text-gray-500">Define el nivel de autorización de esta contraseña</p>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Contraseña *</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Mínimo 6 caracteres</p>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Confirmar contraseña *</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit" class="w-full py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                🔐 Crear contraseña maestra
            </button>
        </form>
    </div>

    {{-- Lista de contraseñas existentes --}}
    @if($contraseñas->count() > 0)
    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <h2 class="mb-6 text-xl font-bold text-slate-800">Mis contraseñas maestras</h2>
        
        <div class="space-y-3">
            @foreach($contraseñas as $item)
            <div class="flex items-center justify-between p-4 border rounded-xl hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 text-indigo-600 bg-indigo-100 rounded-full">
                        @if($item->tipo == 'super_admin')
                        ⭐
                        @else
                        👤
                        @endif
                    </div>
                    <div>
                        <p class="font-medium text-slate-800">
                            {{ $item->tipo == 'super_admin' ? 'Super Administrador' : 'Administrador' }}
                            @if($item->activo)
                            <span class="ml-2 text-xs text-green-600">● Activa</span>
                            @else
                            <span class="ml-2 text-xs text-red-600">● Inactiva</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">
                            Creada: {{ $item->created_at->format('d/m/Y H:i') }}
                            @if($item->ultimo_uso)
                            • Último uso: {{ $item->ultimo_uso->format('d/m/Y H:i') }}
                            @endif
                        </p>
                        @if(auth()->user()->hasRole('Super Admin') && $item->password_texto)
                        <p class="mt-1 font-mono text-xs text-gray-400">Texto: {{ $item->password_texto }}</p>
                        @endif
                    </div>
                </div>
                <form action="{{ route('perfil.contraseñas.destroy', $item) }}" method="POST" 
                      onsubmit="return confirm('¿Eliminar esta contraseña maestra?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2 text-gray-400 transition hover:text-red-600" title="Eliminar">
                        🗑️
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        
        <div class="pt-4 mt-4 border-t">
            <p class="text-xs text-gray-400">
                * Las contraseñas maestras se almacenan de forma segura. Solo el Super Administrador puede ver el texto plano.
            </p>
        </div>
    </div>
    @endif
</div>
@endsection