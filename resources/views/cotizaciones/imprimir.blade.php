{{-- resources/views/cotizaciones/imprimir.blade.php --}}
@php
    $contenido = '
    <div class="row">
        <span>Válida hasta:</span>
        <span>' . ($cotizacion->fecha_validez ? \Carbon\Carbon::parse($cotizacion->fecha_validez)->format('d/m/Y') : 'N/A') . '</span>
    </div>
    <div class="row">
        <span>Atiende:</span>
        <span>' . $cotizacion->usuario->name . '</span>
    </div>
    ' . ($cotizacion->cliente ? '
    <div class="row">
        <span>Cliente:</span>
        <span>' . $cotizacion->cliente->nombre . '</span>
    </div>
    <div class="row">
        <span>RFC:</span>
        <span>' . ($cotizacion->cliente->rfc ?? 'N/A') . '</span>
    </div>
    ' : '') . '
    <div class="divider"></div>
    ';

    foreach ($cotizacion->detalles as $detalle) {
        $contenido .= '
        <div class="row">
            <span>' . $detalle->cantidad . 'x ' . $detalle->producto->nombre . '</span>
            <span>$' . number_format($detalle->subtotal, 2) . '</span>
        </div>
        ';
    }

    $contenido .= '
    <div class="divider"></div>
    <div class="row">
        <span>Subtotal:</span>
        <span>$' . number_format($cotizacion->subtotal, 2) . '</span>
    </div>
    <div class="row">
        <span>IVA (16%):</span>
        <span>$' . number_format($cotizacion->iva, 2) . '</span>
    </div>
    <div class="monto neutro">
        TOTAL: $' . number_format($cotizacion->total, 2) . '
    </div>
    <div class="divider"></div>
    <div class="status status-info">
        ESTADO: ' . strtoupper($cotizacion->estado) . '
    </div>
    ';
@endphp

@extends('tickets.base', [
    'titulo' => 'COTIZACIÓN',
    'numero' => $cotizacion->folio,
    'fecha' => $cotizacion->fecha_cotizacion->format('d/m/Y'),
    'fecha_hora' => $cotizacion->fecha_cotizacion->format('H:i:s'),
    'contenido' => $contenido,
    'auto_imprimir' => true,
    'copias' => 1,
    'config' => $config
])