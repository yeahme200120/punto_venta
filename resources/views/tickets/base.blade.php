<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config->nombre_empresa }} - {{ $titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: {{ $config->fuente ?? 'monospace' }}, 'Courier New', monospace;
            font-size: {{ $config->tamano_fuente ?? 12 }}px;
            line-height: 1.4;
            background: white;
            padding: 10px;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none; }
        }
        @switch($config->ancho_papel ?? '80mm')
            @case('58mm')
                .ticket { max-width: 220px; margin: 0 auto; }
                @break
            @default
                .ticket { max-width: 300px; margin: 0 auto; }
        @endswitch
        .ticket { padding: 10px; border: 1px dashed #ccc; }
        .text-center { text-align: center; }
        .header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header .empresa { font-size: 14px; font-weight: bold; }
        .header .info { font-size: 10px; color: #666; }
        .section { margin: 10px 0; padding: 8px 0; }
        .section-title { font-weight: bold; border-bottom: 1px dotted #ccc; margin-bottom: 8px; padding-bottom: 4px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .row-label { font-weight: bold; }
        .divider { border-top: 1px dashed #ccc; margin: 8px 0; }
        .footer { text-align: center; margin-top: 15px; padding-top: 8px; border-top: 1px dashed #333; font-size: 10px; color: #666; }
        .status { text-align: center; padding: 5px; margin: 8px 0; border-radius: 4px; }
        .status-success { background: #d4edda; color: #155724; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-danger { background: #f8d7da; color: #721c24; }
        .status-info { background: #e3f2fd; color: #1565c0; }
        .status-ingreso { background: #d4edda; color: #155724; }
        .status-egreso { background: #f8d7da; color: #721c24; }
        .monto { font-size: 18px; font-weight: bold; text-align: center; padding: 8px; margin: 10px 0; background: #f8f9fa; }
        .monto.ingreso { color: #28a745; }
        .monto.egreso { color: #dc3545; }
        .monto.neutro { color: #17a2b8; }
        .highlight { text-align: center; padding: 10px; background: #e3f2fd; border-radius: 8px; margin: 10px 0; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; }
        @page { size: auto; margin: 0; }
        .logo { text-align: center; margin-bottom: 8px; }
        .logo img { max-height: 60px; max-width: 100%; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
             {{-- LOGO DE LA EMPRESA --}}
            @if($config->mostrar_logo && $config->logo_url)
                <div class="logo">
                    <img src="{{ $config->logo_url }}" alt="Logo">
                </div>
            @endif
            <div class="empresa">{{ $config->nombre_empresa }}</div>
            @if($config->cabecera)
                <div class="info">{{ $config->cabecera }}</div>
            @endif
            @if($config->mostrar_rfc && $config->rfc)
                <div class="info">RFC: {{ $config->rfc }}</div>
            @endif
            @if($config->mostrar_direccion && $config->direccion)
                <div class="info">📍 {{ $config->direccion }}</div>
            @endif
            @if($config->mostrar_telefono && $config->telefono)
                <div class="info">📞 {{ $config->telefono }}</div>
            @endif
            <div class="divider"></div>
            <div class="info">{{ $titulo }}</div>
            <div class="info">#{{ $numero }}</div>
            <div class="info">{{ $fecha }} {{ $fecha_hora ?? '' }}</div>
        </div>

        <div class="section">
            {!! $contenido !!}
        </div>

        <div class="footer">
            <div class="divider"></div>
            @if($config->footer)
                <div class="info">{{ $config->footer }}</div>
            @endif
            <div class="small">{{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    @if(!$auto_imprimir)
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer;">
            🖨️ Imprimir Ticket
        </button>
    </div>
    @endif

    @if($auto_imprimir)
    <script>
        window.onload = function() {
            window.print();
            @if($copias > 1)
            setTimeout(function() {
                for(var i = 1; i < {{ $copias }}; i++) {
                    window.print();
                }
            }, 500);
            @endif
        }
    </script>
    @endif
</body>
</html>