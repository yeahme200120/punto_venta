{{-- resources/views/cobranza/pagare-ticket.blade.php --}}
@php
    $credito = $pagare->credito;
    $cliente = $credito->cliente;
    
    $contenido = '
    <div class="row">
        <span>Cliente:</span>
        <span>' . $cliente->nombre . '</span>
    </div>
    <div class="row">
        <span>RFC:</span>
        <span>' . ($cliente->rfc ?? 'N/A') . '</span>
    </div>
    <div class="divider"></div>
    <div class="row">
        <span>Número de pago:</span>
        <span>' . $pagare->numero_pago . ' de ' . $credito->num_pagos . '</span>
    </div>
    <div class="row">
        <span>Fecha vencimiento:</span>
        <span>' . $pagare->fecha_vencimiento->format('d/m/Y') . '</span>
    </div>
    <div class="monto neutro">
        MONTO: $' . number_format($pagare->monto, 2) . '
    </div>
    <div class="divider"></div>
    <div class="row">
        <span>Venta original:</span>
        <span>' . $credito->venta->folio . '</span>
    </div>
    <div class="row">
        <span>Total crédito:</span>
        <span>$' . number_format($credito->monto_total, 2) . '</span>
    </div>
    <div class="row">
        <span>Saldo pendiente:</span>
        <span>$' . number_format($credito->saldo_pendiente, 2) . '</span>
    </div>
    <div class="divider"></div>
    <div class="status ' . ($pagare->estado == 'pagado' ? 'status-success' : 'status-warning') . '">
        ESTADO: ' . strtoupper($pagare->estado) . '
    </div>
    ';
@endphp

@extends('tickets.base', [
    'titulo' => 'PAGARÉ',
    'numero' => $pagare->folio,
    'fecha' => $pagare->created_at->format('d/m/Y'),
    'fecha_hora' => $pagare->created_at->format('H:i:s'),
    'contenido' => $contenido,
    'auto_imprimir' => true,
    'copias' => 1,
    'config' => $config
])