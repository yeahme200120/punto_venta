{{-- resources/views/ventas/pagares.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagarés - Crédito #{{ $credito->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .pagare {
            border: 1px solid #333;
            padding: 20px;
            margin-bottom: 20px;
            page-break-after: always;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .cliente-info {
            background: #f5f5f5;
            padding: 10px;
            margin: 15px 0;
        }
        .monto {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: #e8f4f8;
        }
        .firma {
            margin-top: 30px;
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    @foreach($credito->pagares as $pagare)
    <div class="pagare">
        <div class="header">
            <h1>PAGARÉ</h1>
            <p>Folio: {{ $pagare->folio }}</p>
        </div>

        <div class="row">
            <span><strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y') }}</span>
            <span><strong>Fecha de vencimiento:</strong> {{ $pagare->fecha_vencimiento->format('d/m/Y') }}</span>
        </div>

        <div class="cliente-info">
            <div class="row">
                <span><strong>Cliente:</strong> {{ $credito->cliente->nombre }}</span>
                <span><strong>RFC:</strong> {{ $credito->cliente->rfc ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span><strong>Dirección:</strong> {{ $credito->cliente->direccion ?? 'N/A' }}</span>
                <span><strong>Teléfono:</strong> {{ $credito->cliente->telefono ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="monto">
            MONTO: ${{ number_format($pagare->monto, 2) }}
        </div>

        <div class="row">
            <span><strong>Número de pago:</strong> {{ $pagare->numero_pago }} de {{ $credito->num_pagos }}</span>
            <span><strong>Total del crédito:</strong> ${{ number_format($credito->monto_total, 2) }}</span>
        </div>

        <div class="row">
            <span><strong>Venta asociada:</strong> {{ $credito->venta->folio }}</span>
            <span><strong>Plazo:</strong> {{ str_replace('_', ' ', $credito->plazo) }}</span>
        </div>

        <div class="firma">
            <p>_________________________</p>
            <p>Firma del cliente</p>
        </div>

        <div class="footer">
            <p>Este documento es un pagaré que ampara la venta a crédito. El cliente se compromete a pagar en la fecha estipulada.</p>
        </div>
    </div>
    @endforeach
</body>
</html>