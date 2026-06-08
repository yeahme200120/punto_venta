{{-- resources/views/cotizaciones/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $cotizacion->folio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            padding: 30px;
            position: relative;
        }
        /* Marca de agua */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(200, 200, 200, 0.3);
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header .empresa {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        .header .ruc {
            font-size: 10px;
            color: #666;
        }
        .titulo {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            padding: 8px;
            background: #f3f4f6;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 10px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background: #f3f4f6;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totales {
            width: 300px;
            margin-left: auto;
            margin-top: 15px;
        }
        .totales tr td {
            padding: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .nota {
            margin-top: 20px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffecb3;
            border-radius: 5px;
            font-size: 10px;
            text-align: center;
        }
        @page {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="watermark">COTIZACIÓN</div>
    <div class="watermark" style="top: 60%">PRECIOS SUJETOS A CAMBIOS SIN PREVIO AVISO</div>

    <div class="header">
        <div class="empresa">{{ $empresa->nombre ?? 'Mi Empresa' }}</div>
        <div class="ruc">RFC: {{ $empresa->rfc ?? 'N/A' }}</div>
        <div class="ruc">{{ $empresa->direccion ?? '' }}</div>
        <div class="ruc">Tel: {{ $empresa->telefono ?? '' }} | Email: {{ $empresa->correo ?? '' }}</div>
    </div>

    <div class="titulo">
        COTIZACIÓN {{ $cotizacion->folio }}
    </div>

    <div class="info-section">
        <div class="info-row">
            <span><strong>Fecha de Emisión:</strong> {{ $cotizacion->fecha_cotizacion->format('d/m/Y H:i') }}</span>
            <span><strong>Válida hasta:</strong> {{ $cotizacion->fecha_validez ? \Carbon\Carbon::parse($cotizacion->fecha_validez)->format('d/m/Y') : 'No especificada' }}</span>
        </div>
        <div class="info-row">
            <span><strong>Atendió:</strong> {{ $cotizacion->usuario->name }}</span>
            <span><strong>Estado:</strong> {{ ucfirst($cotizacion->estado) }}</span>
        </div>
        @if($cotizacion->cliente)
        <div class="info-row">
            <span><strong>Cliente:</strong> {{ $cotizacion->cliente->nombre }}</span>
            <span><strong>RFC:</strong> {{ $cotizacion->cliente->rfc ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span><strong>Dirección:</strong> {{ $cotizacion->cliente->direccion ?? 'N/A' }}</span>
            <span><strong>Teléfono:</strong> {{ $cotizacion->cliente->telefono ?? 'N/A' }}</span>
        </div>
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Cantidad</th>
                <th>Producto</th>
                <th class="text-right">Precio Unitario</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->detalles as $detalle)
            <tr>
                <td>{{ $detalle->cantidad }}</td>
                <td>{{ $detalle->producto->nombre }}</td>
                <td class="text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="text-right">${{ number_format($cotizacion->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td><strong>IVA (16%):</strong></td>
            <td class="text-right">${{ number_format($cotizacion->iva, 2) }}</td>
        </tr>
        <tr style="border-top: 1px solid #333;">
            <td><strong>TOTAL:</strong></td>
            <td class="text-right"><strong>${{ number_format($cotizacion->total, 2) }}</strong></td>
        </tr>
    </table>

    <div class="nota">
        <strong>NOTA IMPORTANTE:</strong> Esta cotización es válida por {{ $diasValidez ?? '7' }} días a partir de la fecha de emisión.<br>
        Los precios están sujetos a cambios sin previo aviso. Aplica restricciones.
    </div>

    <div class="footer">
        <p>Documento generado electrónicamente - {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>{{ $empresa->nombre ?? 'Mi Empresa' }} - Todos los derechos reservados</p>
    </div>
</body>
</html>