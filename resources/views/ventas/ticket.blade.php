{{-- resources/views/ventas/ticket.blade.php --}}
@php
    // Construir contenido del ticket de venta
    $contenido = '
    <div class="row">
        <span>Atiende:</span>
        <span>' . $venta->usuario->name . '</span>
    </div>
    ' . ($venta->cliente ? '
    <div class="row">
        <span>Cliente:</span>
        <span>' . $venta->cliente->nombre . '</span>
    </div>
    ' : '') . '
    <div class="divider"></div>
    ';

    foreach ($venta->detalles as $detalle) {
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
        <span>$' . number_format($venta->subtotal, 2) . '</span>
    </div>
    <div class="row">
        <span>IVA (16%):</span>
        <span>$' . number_format($venta->iva, 2) . '</span>
    </div>
    <div class="monto neutro">
        TOTAL: $' . number_format($venta->total, 2) . '
    </div>
    ' . ($venta->tipo == 'credito' ? '
    <div class="status status-warning">
        VENTA A CRÉDITO
    </div>
    ' : '');
@endphp

@extends('tickets.base', [
    'titulo' => 'TICKET DE VENTA',
    'numero' => $venta->folio,
    'fecha' => $venta->created_at->format('d/m/Y'),
    'fecha_hora' => $venta->created_at->format('H:i:s'),
    'contenido' => $contenido,
    'auto_imprimir' => true,
    'copias' => 1,
    'config' => $config
])