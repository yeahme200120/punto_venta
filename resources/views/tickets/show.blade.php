@extends('layouts.app')
@section('title', 'Detalle de Configuración')
@section('page-title', 'Detalle de Configuración')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li><li><a href="{{ route('ticket.index') }}" class="text-gray-500 hover:text-indigo-600">Tickets</a></li><li><span class="text-gray-400">/</span></li><li><span class="font-medium text-gray-700">Ver</span></li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="p-6 border-b bg-gray-50"><h3 class="text-lg font-bold">Configuración para {{ ucfirst($ticketConfiguracion->tipo) }}</h3></div>
        <div class="grid gap-4 p-6 md:grid-cols-2">
            <div><span class="font-medium">Empresa:</span> {{ $ticketConfiguracion->nombre_empresa ?? '—' }}</div>
            <div><span class="font-medium">RFC:</span> {{ $ticketConfiguracion->rfc ?? '—' }}</div>
            <div><span class="font-medium">Dirección:</span> {{ $ticketConfiguracion->direccion ?? '—' }}</div>
            <div><span class="font-medium">Teléfono:</span> {{ $ticketConfiguracion->telefono ?? '—' }}</div>
            <div><span class="font-medium">Email:</span> {{ $ticketConfiguracion->email ?? '—' }}</div>
            <div><span class="font-medium">Cabecera:</span> {{ $ticketConfiguracion->cabecera ?? '—' }}</div>
            <div><span class="font-medium">Footer:</span> {{ $ticketConfiguracion->footer ?? '—' }}</div>
            <div><span class="font-medium">Ancho papel:</span> {{ $ticketConfiguracion->ancho_papel }}</div>
            <div><span class="font-medium">Fuente:</span> {{ $ticketConfiguracion->fuente }}</div>
            <div><span class="font-medium">Tamaño fuente:</span> {{ $ticketConfiguracion->tamano_fuente }}px</div>
            <div><span class="font-medium">Copias:</span> {{ $ticketConfiguracion->copias }}</div>
            <div><span class="font-medium">Auto imprimir:</span> {{ $ticketConfiguracion->auto_imprimir ? 'Sí' : 'No' }}</div>
            <div><span class="font-medium">Facturar:</span> {{ $ticketConfiguracion->facturar ? 'Sí' : 'No' }}</div>
            <div><span class="font-medium">Activo:</span> {{ $ticketConfiguracion->activo ? '✅ Sí' : '❌ No' }}</div>
            <div class="md:col-span-2"><span class="font-medium">Logo:</span> @if($ticketConfiguracion->logo_url && $ticketConfiguracion->mostrar_logo)<img src="{{ $ticketConfiguracion->logo_url }}" class="h-12">@else Sin logo @endif</div>
        </div>
        <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
            <a href="{{ route('ticket.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Volver</a>
            <a href="{{ route('ticket.edit', $ticketConfiguracion) }}" class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Editar</a>
        </div>
    </div>
</div>
@endsection