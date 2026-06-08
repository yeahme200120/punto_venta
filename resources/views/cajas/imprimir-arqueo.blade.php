<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arqueo de Caja - {{ $arqueo->created_at->format('d/m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            padding: 20px;
            background: white;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 12px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dotted #ccc;
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            font-size: 12px;
        }
        .info-value {
            font-size: 12px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .detalle-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .detalle-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .detalle-table td:first-child {
            font-weight: bold;
        }
        .detalle-table td:last-child {
            text-align: right;
        }
        .totales {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .total-row.total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            margin-top: 8px;
            padding-top: 12px;
        }
        .diferencia {
            background: #e8f5e9;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            text-align: center;
        }
        .diferencia.sobrante { background: #e8f5e9; color: #2e7d32; }
        .diferencia.faltante { background: #ffebee; color: #c62828; }
        .diferencia.cuadrado { background: #e3f2fd; color: #1565c0; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
        }
        .observaciones {
            margin-top: 20px;
            padding: 12px;
            background: #fff3e0;
            border-radius: 8px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        {{-- Header --}}
        <div class="header">
            <h1>📊 ARQUEO DE CAJA</h1>
            <p>Fecha y hora: {{ $arqueo->created_at->format('d/m/Y H:i:s') }}</p>
            <p>Estado: {{ $arqueo->estado == 'finalizado' ? 'FINALIZADO' : 'BORRADOR' }}</p>
        </div>

        {{-- Información --}}
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Caja:</span>
                <span class="info-value">{{ $arqueo->cajaApertura->caja->nombre }} ({{ $arqueo->cajaApertura->caja->codigo }})</span>
            </div>
            <div class="info-item">
                <span class="info-label">Usuario:</span>
                <span class="info-value">{{ $arqueo->usuario->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Sucursal:</span>
                <span class="info-value">{{ $arqueo->sucursal->nombre ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Apertura:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($arqueo->cajaApertura->fecha_apertura)->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Desglose --}}
        <div class="section-title">💰 Desglose por forma de pago</div>
        <table class="detalle-table">
            @php
                $montos = [
                    'efectivo' => ['icono' => '💵', 'nombre' => 'Efectivo'],
                    'tarjeta_debito' => ['icono' => '💳', 'nombre' => 'Tarjeta Débito'],
                    'tarjeta_credito' => ['icono' => '💎', 'nombre' => 'Tarjeta Crédito'],
                    'vale' => ['icono' => '🎫', 'nombre' => 'Vale'],
                    'transferencia' => ['icono' => '🏦', 'nombre' => 'Transferencia'],
                    'cheque' => ['icono' => '📄', 'nombre' => 'Cheque'],
                ];
            @endphp
            @foreach($montos as $clave => $info)
                @php $valor = $arqueo->{$clave . '_contado'} ?? 0; @endphp
                @if($valor > 0)
                <tr>
                    <td>{{ $info['icono'] }} {{ $info['nombre'] }} contado</td>
                    <td>${{ number_format($valor, 2) }}</td>
                </tr>
                @endif
            @endforeach
        </table>

        {{-- Totales --}}
        <div class="section-title">📊 Totales</div>
        <div class="totales">
            <div class="total-row">
                <span>Total contado (físico):</span>
                <span><strong>${{ number_format($arqueo->total_contado, 2) }}</strong></span>
            </div>
            <div class="total-row">
                <span>Total según sistema:</span>
                <span><strong>${{ number_format($arqueo->total_sistema, 2) }}</strong></span>
            </div>
        </div>

        {{-- Diferencia --}}
        @php
            $diferencia = $arqueo->diferencia;
            $clase = $diferencia > 0 ? 'sobrante' : ($diferencia < 0 ? 'faltante' : 'cuadrado');
            $texto = $diferencia > 0 ? 'SOBRANTE' : ($diferencia < 0 ? 'FALTANTE' : 'CUADRADO');
        @endphp
        <div class="diferencia {{ $clase }}">
            <strong>DIFERENCIA: {{ $texto }}</strong><br>
            {{ $diferencia >= 0 ? '+' : '' }}${{ number_format(abs($diferencia), 2) }}
        </div>

        {{-- Observaciones --}}
        @if($arqueo->observaciones)
        <div class="section-title">📝 Observaciones</div>
        <div class="observaciones">
            {{ $arqueo->observaciones }}
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>Documento generado automáticamente - {{ now()->format('d/m/Y H:i:s') }}</p>
            <p style="margin-top: 20px;">
                _________________________<br>
                Firma del responsable
            </p>
        </div>

        {{-- Botón imprimir --}}
        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer;">
                🖨️ Imprimir / Guardar PDF
            </button>
        </div>
    </div>
</body>
</html>