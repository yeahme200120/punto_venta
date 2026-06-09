@extends('layouts.app')

@section('title', 'Diseño de Tickets')
@section('page-title', 'Diseño de Tickets')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><a href="{{ route('ticket.index') }}" class="text-gray-500 hover:text-indigo-600">Tickets</a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Diseño</span></li>
@endsection

@section('content')
<style>
    .logo-preview img {
        max-height: 60px;
        max-width: 100%;
    }
</style>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="p-6 bg-white border shadow-sm rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">Previsualización de Ticket</h2>
            <select id="tipoSelector" class="px-4 py-2 bg-white border rounded-xl">
                <option value="">Selecciona un tipo de ticket</option>
                @foreach($configuraciones as $cfg)
                    <option value="{{ $cfg->id }}" {{ $config && $config->id == $cfg->id ? 'selected' : '' }}>
                        {{ ucfirst($cfg->tipo) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Vista previa del ticket -->
        <div id="ticketPreview" class="flex justify-center">
            <div class="p-4 bg-gray-100 shadow-inner rounded-xl">
                <div class="ticket-container"
                    style="max-width: 400px; background: white; padding: 15px; border-radius: 12px;">
                    <div class="pb-3 mb-3 text-center border-b">
                        <div id="previewLogo" class="mb-2 logo-preview"></div>
                        <div id="previewEmpresa" class="text-lg font-bold"></div>
                        <div id="previewCabecera" class="text-sm text-gray-500"></div>
                        <div id="previewRfc" class="text-xs text-gray-400"></div>
                        <div id="previewDireccion" class="text-xs text-gray-400"></div>
                        <div id="previewTelefono" class="text-xs text-gray-400"></div>
                    </div>
                    <div id="previewContenido" class="min-h-[200px] text-sm">
                        <!-- Contenido simulado -->
                        <div class="flex justify-between mb-1"><span>Producto 1</span><span>$100.00</span></div>
                        <div class="flex justify-between mb-1"><span>Producto 2</span><span>$200.00</span></div>
                        <div class="my-2 border-t"></div>
                        <div class="flex justify-between font-bold"><span>TOTAL</span><span>$300.00</span></div>
                    </div>
                    <div id="previewFooter" class="pt-3 mt-3 text-xs text-center text-gray-400 border-t"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white border shadow-sm rounded-2xl">
        <h3 class="mb-4 font-semibold">Información de la configuración actual</h3>
        <div class="grid gap-4 text-sm md:grid-cols-2">
            <div><strong>Tipo:</strong> <span id="infoTipo">-</span></div>
            <div><strong>Empresa:</strong> <span id="infoEmpresa">-</span></div>
            <div><strong>Ancho papel:</strong> <span id="infoAncho">-</span></div>
            <div><strong>Fuente:</strong> <span id="infoFuente">-</span></div>
            <div><strong>Tamaño fuente:</strong> <span id="infoTamano">-</span>px</div>
            <div><strong>Auto imprimir:</strong> <span id="infoAutoImprimir">-</span></div>
            <div><strong>Copias:</strong> <span id="infoCopias">-</span></div>
        </div>
    </div>
</div>

<script>
    const configs = @json($configuraciones);
    const DEFAULT_LOGO = "{{ asset('logo/LogoAdmin.png') }}";

    function updatePreview(id) {
        const config = configs.find(c => c.id == id);
        if (!config) return;

        // Determinar la URL del logo (prioridad: propio > empresa > por defecto)
        let logoUrl = DEFAULT_LOGO;
        if (config.logo_url) {
            logoUrl = config.logo_url;
        } else if (config.empresa && config.empresa.logo_url) {
            logoUrl = config.empresa.logo_url;
        }

        const logoHtml = (config.mostrar_logo) ? `<img src="${logoUrl}" class="h-12 mx-auto mb-2">` : '';
        document.getElementById('previewLogo').innerHTML = logoHtml;
        document.getElementById('previewEmpresa').innerText = config.nombre_empresa || 'Mi Empresa';
        document.getElementById('previewCabecera').innerText = config.cabecera || '';
        document.getElementById('previewRfc').innerText = (config.mostrar_rfc && config.rfc) ? `RFC: ${config.rfc}` : '';
        document.getElementById('previewDireccion').innerText = (config.mostrar_direccion && config.direccion) ? config.direccion : '';
        document.getElementById('previewTelefono').innerText = (config.mostrar_telefono && config.telefono) ? `📞 ${config.telefono}` : '';
        document.getElementById('previewFooter').innerText = config.footer || '';

        // Información
        document.getElementById('infoTipo').innerText = config.tipo;
        document.getElementById('infoEmpresa').innerText = config.nombre_empresa || '-';
        document.getElementById('infoAncho').innerText = config.ancho_papel;
        document.getElementById('infoFuente').innerText = config.fuente;
        document.getElementById('infoTamano').innerText = config.tamano_fuente;
        document.getElementById('infoAutoImprimir').innerText = config.auto_imprimir ? 'Sí' : 'No';
        document.getElementById('infoCopias').innerText = config.copias;

        // Aplicar estilos al contenedor
        const container = document.querySelector('.ticket-container');
        container.style.fontFamily = (config.fuente === 'monospace') ? "'Courier New', monospace" : config.fuente;
        container.style.fontSize = config.tamano_fuente + 'px';
        if (config.ancho_papel === '58mm') {
            container.style.maxWidth = '220px';
        } else {
            container.style.maxWidth = '400px';
        }
    }

    document.getElementById('tipoSelector').addEventListener('change', function () {
        if (this.value) updatePreview(this.value);
    });

    // Cargar la primera configuración si existe
    if (configs.length) updatePreview(configs[0].id);
</script>
@endsection