{{-- resources/views/formas_pago/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle de Forma de Pago')
@section('page-title', 'Detalle de Forma de Pago')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><a href="{{ route('formas_pago.index') }}" class="text-gray-500 hover:text-indigo-600">Formas de Pago</a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">{{ $formaPago->nombre }}</span></li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="overflow-hidden bg-white shadow-xl rounded-3xl">
        
        {{-- Header con gradiente --}}
        <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-cyan-500">
            <div class="flex items-center gap-6">
                <div class="flex items-center justify-center w-20 h-20 text-4xl border-2 rounded-full shadow-lg bg-white/20 backdrop-blur-sm border-white/30">
                    {{ $formaPago->icono ?? '💳' }}
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-white">{{ $formaPago->nombre }}</h2>
                    <p class="font-mono text-indigo-100">{{ $formaPago->clave }}</p>
                    <div class="flex flex-wrap gap-3 mt-3">
                        <span class="flex items-center gap-1 px-3 py-1 text-sm text-white rounded-full bg-white/20">
                            🔢 Orden: {{ $formaPago->orden }}
                        </span>
                        @if($formaPago->activo_global)
                            <span class="flex items-center gap-1 px-3 py-1 text-sm text-white rounded-full bg-green-500/40">
                                🟢 Activo globalmente
                            </span>
                        @else
                            <span class="flex items-center gap-1 px-3 py-1 text-sm text-white rounded-full bg-red-500/40">
                                🔴 Inactivo globalmente
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-white">#{{ $formaPago->id }}</div>
                    <p class="text-sm text-indigo-100">ID de catálogo</p>
                </div>
            </div>
        </div>

        {{-- Contenido principal --}}
        <div class="p-8">
            {{-- Información general --}}
            <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
                <div class="p-5 border border-gray-100 bg-gray-50 rounded-2xl">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex items-center justify-center w-10 h-10 bg-indigo-100 rounded-xl">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Información general</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Clave:</span>
                            <span class="font-mono font-medium text-gray-800">{{ $formaPago->clave }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Nombre:</span>
                            <span class="font-medium text-gray-800">{{ $formaPago->nombre }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Icono:</span>
                            <span class="text-2xl">{{ $formaPago->icono ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Orden global:</span>
                            <span class="font-medium text-gray-800">{{ $formaPago->orden }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-5 border border-gray-100 bg-gray-50 rounded-2xl">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-100">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Requisitos</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Requiere referencia:</span>
                            @if($formaPago->requiere_referencia)
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">✅ Sí</span>
                            @else
                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-200 rounded-full">❌ No</span>
                            @endif
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Requiere autorización:</span>
                            @if($formaPago->requiere_autorizacion)
                                <span class="px-2 py-1 text-xs text-orange-700 bg-orange-100 rounded-full">🔐 Sí</span>
                            @else
                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-200 rounded-full">❌ No</span>
                            @endif
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Estado global:</span>
                            @if($formaPago->activo_global)
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">🟢 Activo</span>
                            @else
                                <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">🔴 Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Empresas donde está activa --}}
            <div class="p-5 mb-8 border border-gray-100 bg-gray-50 rounded-2xl">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-xl">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Empresas donde está activa</h3>
                    <span class="ml-auto text-sm text-gray-500">Total: {{ $empresasActivas->count() }}</span>
                </div>

                @if($empresasActivas->count() > 0)
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach($empresasActivas as $empresaConfig)
                            <div class="flex items-center justify-between p-3 transition bg-white border border-gray-200 rounded-xl hover:shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 text-lg bg-indigo-100 rounded-full">
                                        🏢
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $empresaConfig->empresa->nombre ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">Orden: {{ $empresaConfig->orden_empresa }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($empresaConfig->activo)
                                        <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full">Activa</span>
                                    @else
                                        <span class="px-2 py-1 text-xs text-red-700 bg-red-100 rounded-full">Inactiva</span>
                                    @endif
                                    <a href="{{ route('formas_pago.configurar.empresa', $empresaConfig->empresa_id) }}" 
                                       class="p-1 text-gray-400 transition hover:text-indigo-600" title="Configurar empresa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <p>No hay empresas configuradas con esta forma de pago</p>
                    </div>
                @endif
            </div>

            {{-- Fechas de creación/actualización --}}
            <div class="flex items-center justify-between pt-4 text-xs text-gray-400 border-t">
                <div>
                    <span>Creado: {{ $formaPago->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div>
                    <span>Última actualización: {{ $formaPago->updated_at->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 mt-6 border-t">
                <a href="{{ route('formas_pago.index') }}" 
                   class="flex items-center gap-2 px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver al listado
                </a>
                <div class="flex gap-3">
                    <a href="{{ route('formas_pago.edit', $formaPago) }}" 
                       class="flex items-center gap-2 px-6 py-3 font-medium text-white transition shadow-md bg-amber-500 rounded-xl hover:bg-amber-600">
                        ✏️ Editar forma de pago
                    </a>
                    <a href="{{ route('formas_pago.configurar.empresa', session('empresa_activa_id')) }}" 
                       class="flex items-center gap-2 px-6 py-3 font-medium text-white transition shadow-md bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                        ⚙️ Configurar por empresa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection