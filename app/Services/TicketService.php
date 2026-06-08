<?php
// app/Services/TicketService.php

namespace App\Services;

use App\Models\TicketConfiguracion;
use Illuminate\Support\Facades\View;

class TicketService
{
    protected $empresaId;

    public function __construct()
    {
        $this->empresaId = auth()->user()->empresa_id ?? 1;
    }

    /**
     * Generar ticket de movimiento
     */
    public function movimientoTicket($movimiento)
    {
        $config = TicketConfiguracion::firstOrCreate(
            ['empresa_id' => $this->empresaId, 'tipo' => 'movimiento'],
            $this->getDefaultConfig('movimiento')
        );

        $contenido = $this->buildMovimientoContent($movimiento);

        return $this->render([
            'titulo' => 'COMPROBANTE DE MOVIMIENTO',
            'numero' => str_pad($movimiento->id, 8, '0', STR_PAD_LEFT),
            'fecha' => $movimiento->created_at->format('d/m/Y'),
            'fecha_hora' => $movimiento->created_at->format('d/m/Y H:i:s'),
            'contenido' => $contenido,
            'auto_imprimir' => $config->auto_imprimir,
            'copias' => $config->copias,
            'config' => $config
        ]);
    }

    /**
     * Generar ticket de transferencia
     */
    public function transferenciaTicket($transferencia)
    {
        $config = TicketConfiguracion::firstOrCreate(
            ['empresa_id' => $this->empresaId, 'tipo' => 'transferencia'],
            $this->getDefaultConfig('transferencia')
        );

        $contenido = $this->buildTransferenciaContent($transferencia);

        return $this->render([
            'titulo' => 'COMPROBANTE DE TRANSFERENCIA',
            'numero' => str_pad($transferencia->id, 8, '0', STR_PAD_LEFT),
            'fecha' => $transferencia->created_at->format('d/m/Y'),
            'fecha_hora' => $transferencia->created_at->format('d/m/Y H:i:s'),
            'contenido' => $contenido,
            'auto_imprimir' => $config->auto_imprimir,
            'copias' => $config->copias,
            'config' => $config
        ]);
    }

    /**
     * Generar ticket de arqueo
     */
    public function arqueoTicket($arqueo)
    {
        $config = TicketConfiguracion::firstOrCreate(
            ['empresa_id' => $this->empresaId, 'tipo' => 'arqueo'],
            $this->getDefaultConfig('arqueo')
        );

        $contenido = $this->buildArqueoContent($arqueo);

        return $this->render([
            'titulo' => 'ARQUEO DE CAJA',
            'numero' => str_pad($arqueo->id, 8, '0', STR_PAD_LEFT),
            'fecha' => $arqueo->created_at->format('d/m/Y'),
            'fecha_hora' => $arqueo->created_at->format('d/m/Y H:i:s'),
            'contenido' => $contenido,
            'auto_imprimir' => $config->auto_imprimir,
            'copias' => $config->copias,
            'config' => $config
        ]);
    }

    /**
     * Generar ticket de cierre de caja
     */
    public function cierreTicket($apertura, $resumen)
    {
        $config = TicketConfiguracion::firstOrCreate(
            ['empresa_id' => $this->empresaId, 'tipo' => 'cierre'],
            $this->getDefaultConfig('cierre')
        );

        $contenido = $this->buildCierreContent($apertura, $resumen);

        return $this->render([
            'titulo' => 'CIERRE DE CAJA',
            'numero' => str_pad($apertura->id, 8, '0', STR_PAD_LEFT),
            'fecha' => $apertura->fecha->format('d/m/Y'),
            'fecha_hora' => now()->format('d/m/Y H:i:s'),
            'contenido' => $contenido,
            'auto_imprimir' => $config->auto_imprimir,
            'copias' => $config->copias,
            'config' => $config
        ]);
    }

    /**
     * Configuración por defecto para cada tipo de ticket
     */
    protected function getDefaultConfig(string $tipo): array
    {
        $empresa = auth()->user()?->empresa;

        return [
            'nombre_empresa' => $empresa?->nombre ?? 'Mi Empresa',
            'logo_url' => $empresa?->logo_url,
            'direccion' => $empresa?->direccion,
            'telefono' => $empresa?->telefono,
            'email' => $empresa?->correo,
            'rfc' => $empresa?->rfc,

            'cabecera' => $this->getCabeceraPorTipo($tipo),
            'footer' => '¡Gracias por su preferencia!',

            'mostrar_logo' => true,
            'mostrar_direccion' => true,
            'mostrar_telefono' => true,
            'mostrar_email' => true,
            'mostrar_rfc' => true,
            'mostrar_regimen' => false,

            'ancho_papel' => '80mm',
            'fuente' => 'monospace',
            'tamano_fuente' => 12,

            'regimen_fiscal' => null,
            'uso_cfdi' => null,

            'auto_imprimir' => true,
            'facturar' => true,
            'copias' => 1,

            'activo' => true,
        ];
    }

    /**
     * Construir contenido para movimiento
     */
    protected function buildMovimientoContent($movimiento)
    {
        $tipoClass = $movimiento->tipo == 'ingreso' ? 'ingreso' : 'egreso';
        $tipoIcono = $movimiento->tipo == 'ingreso' ? '💰' : '💸';
        $tipoTexto = strtoupper($movimiento->tipo);
        $formaPago = $this->getFormaPagoTexto($movimiento->forma_pago);
        $categoria = ucfirst(str_replace('_', ' ', $movimiento->categoria));

        return "
            <div class='status status-{$tipoClass}'>
                <strong>{$tipoIcono} {$tipoTexto}</strong>
            </div>
            
            <div class='monto {$tipoClass}'>
                {$tipoIcono} $" . number_format($movimiento->monto, 2) . "
            </div>
            
            <div class='row'>
                <span class='row-label'>Caja:</span>
                <span>{$movimiento->cajaApertura->caja->nombre}</span>
            </div>
            <div class='row'>
                <span class='row-label'>Usuario:</span>
                <span>{$movimiento->usuario->name}</span>
            </div>
            <div class='row'>
                <span class='row-label'>Concepto:</span>
                <span>{$movimiento->concepto}</span>
            </div>
            <div class='row'>
                <span class='row-label'>Categoría:</span>
                <span>{$categoria}</span>
            </div>
            <div class='row'>
                <span class='row-label'>Forma de pago:</span>
                <span>{$formaPago}</span>
            </div>
        " . ($movimiento->referencia ? "
            <div class='row'>
                <span class='row-label'>Referencia:</span>
                <span>{$movimiento->referencia}</span>
            </div>
        " : "") . "
            <div class='divider'></div>
            <div class='row small'>
                <span>Ticket #" . str_pad($movimiento->id, 8, '0', STR_PAD_LEFT) . "</span>
            </div>
        ";
    }

    /**
     * Construir contenido para transferencia
     */
    protected function buildTransferenciaContent($transferencia)
    {
        $estadoClass = $this->getEstadoClass($transferencia->estado);
        $estadoTexto = $this->getEstadoTexto($transferencia->estado);
        $transferencia->autorizador->name = $transferencia->autorizador->name ?? 'NA';

        return "
            <div class='status {$estadoClass}'>
                <strong>{$estadoTexto}</strong>
            </div>
            
            <div class='highlight'>
                <div><strong>📤 ORIGEN</strong></div>
                <div>{$transferencia->cajaOrigen->nombre}</div>
                <div class='small'>({$transferencia->cajaOrigen->codigo})</div>
                <div class='divider'></div>
                <div>⬇️</div>
                <div class='divider'></div>
                <div><strong>📥 DESTINO</strong></div>
                <div>{$transferencia->cajaDestino->nombre}</div>
                <div class='small'>({$transferencia->cajaDestino->codigo})</div>
            </div>
            
            <div class='monto neutro'>
                💰 $" . number_format($transferencia->monto, 2) . "
            </div>
            
            <div class='row'>
                <span class='row-label'>Solicitante:</span>
                <span>{$transferencia->usuario->name}</span>
            </div>
            <div class='row'>
                <span class='row-label'>Motivo:</span>
                <span>{$transferencia->motivo}</span>
            </div>
        " . ($transferencia->autorizado_por ? "
            <div class='row'>
                <span class='row-label'>Autorizado por:</span>
                <span>{$transferencia->autorizador->name}</span>
            </div>
            <div class='row'>
                <span class='row-label'>Fecha aut.:</span>
                <span>" . ($transferencia->autorizado_en ? \Carbon\Carbon::parse($transferencia->autorizado_en)->format('d/m/Y H:i') : 'N/A') . "</span>
            </div>
        " : "");
    }

    /**
     * Construir contenido para arqueo
     */
    protected function buildArqueoContent($arqueo)
    {
        $diferenciaClass = $arqueo->diferencia > 0 ? 'status-success' : ($arqueo->diferencia < 0 ? 'status-danger' : 'status-info');
        $diferenciaTexto = $arqueo->diferencia > 0 ? 'SOBRANTE' : ($arqueo->diferencia < 0 ? 'FALTANTE' : 'CUADRADO');

        return "
            <div class='status {$diferenciaClass}'>
                <strong>DIFERENCIA: {$diferenciaTexto}</strong>
            </div>
            
            <div class='section-title'>💰 DESGLOSE</div>
            <div class='row'>
                <span>💵 Efectivo:</span>
                <span>$" . number_format($arqueo->efectivo_contado, 2) . "</span>
            </div>
            <div class='row'>
                <span>💳 Tarjeta Débito:</span>
                <span>$" . number_format($arqueo->tarjeta_debito_contado, 2) . "</span>
            </div>
            <div class='row'>
                <span>💎 Tarjeta Crédito:</span>
                <span>$" . number_format($arqueo->tarjeta_credito_contado, 2) . "</span>
            </div>
            <div class='row'>
                <span>🎫 Vale:</span>
                <span>$" . number_format($arqueo->vale_contado, 2) . "</span>
            </div>
            <div class='row'>
                <span>🏦 Transferencia:</span>
                <span>$" . number_format($arqueo->transferencia_contado, 2) . "</span>
            </div>
            <div class='row'>
                <span>📄 Cheque:</span>
                <span>$" . number_format($arqueo->cheque_contado, 2) . "</span>
            </div>
            <div class='divider'></div>
            <div class='row bold'>
                <span>TOTAL CONTADO:</span>
                <span>$" . number_format($arqueo->total_contado, 2) . "</span>
            </div>
            <div class='row bold'>
                <span>TOTAL SISTEMA:</span>
                <span>$" . number_format($arqueo->total_sistema, 2) . "</span>
            </div>
            <div class='row bold'>
                <span>DIFERENCIA:</span>
                <span>" . ($arqueo->diferencia >= 0 ? '+' : '') . "$" . number_format($arqueo->diferencia, 2) . "</span>
            </div>
        " . ($arqueo->observaciones ? "
            <div class='divider'></div>
            <div class='section-title'>📝 OBSERVACIONES</div>
            <div class='small'>{$arqueo->observaciones}</div>
        " : "");
    }

    /**
     * Construir contenido para cierre de caja
     */
    protected function buildCierreContent($apertura, $resumen)
    {
        $formaPagoHtml = '';
        foreach ($resumen['por_forma_pago'] as $forma => $monto) {
            if ($monto > 0) {
                $icono = $this->getFormaPagoIcono($forma);
                $texto = $this->getFormaPagoTexto($forma);
                $formaPagoHtml .= "<div class='row'><span>{$icono} {$texto}:</span><span>$" . number_format($monto, 2) . "</span></div>";
            }
        }

        return "
            <div class='row'>
                <span class='row-label'>Caja:</span>
                <span>{$apertura->caja->nombre}</span>
            </div>
            <div class='row'>
                <span class='row-label'>Usuario:</span>
                <span>{$apertura->usuario->name}</span>
            </div>
            <div class='divider'></div>
            
            <div class='row'>
                <span>Monto Inicial:</span>
                <span>$" . number_format($resumen['apertura'], 2) . "</span>
            </div>
            <div class='row'>
                <span>Total Ingresos:</span>
                <span>+ $" . number_format($resumen['total_ingresos'], 2) . "</span>
            </div>
            <div class='row'>
                <span>Total Egresos:</span>
                <span>- $" . number_format($resumen['total_egresos'], 2) . "</span>
            </div>
            <div class='divider'></div>
            <div class='row bold'>
                <span>SALDO FINAL:</span>
                <span>$" . number_format($resumen['saldo_esperado'], 2) . "</span>
            </div>
            <div class='divider'></div>
            
            <div class='section-title'>📊 POR FORMA DE PAGO</div>
            {$formaPagoHtml}
        ";
    }

    /**
     * Renderizar la vista del ticket
     */
    protected function render($data)
    {
        return View::make('tickets.base', $data);
    }

    /**
     * Obtener icono de forma de pago
     */
    protected function getFormaPagoIcono($forma)
    {
        return match ($forma) {
            'efectivo' => '💵',
            'tarjeta_debito' => '💳',
            'tarjeta_credito' => '💎',
            'vale' => '🎫',
            'transferencia' => '🏦',
            'cheque' => '📄',
            default => '💰'
        };
    }

    /**
     * Obtener texto de forma de pago
     */
    protected function getFormaPagoTexto($forma)
    {
        return match ($forma) {
            'efectivo' => 'Efectivo',
            'tarjeta_debito' => 'Tarjeta Débito',
            'tarjeta_credito' => 'Tarjeta Crédito',
            'vale' => 'Vale',
            'transferencia' => 'Transferencia',
            'cheque' => 'Cheque',
            default => ucfirst($forma)
        };
    }

    /**
     * Obtener clase CSS según estado
     */
    protected function getEstadoClass($estado)
    {
        return match ($estado) {
            'aprobada' => 'status-success',
            'pendiente' => 'status-warning',
            'rechazada' => 'status-danger',
            default => 'status-info'
        };
    }

    /**
     * Obtener texto según estado
     */
    protected function getEstadoTexto($estado)
    {
        return match ($estado) {
            'aprobada' => '✅ APROBADA',
            'pendiente' => '⏳ PENDIENTE',
            'rechazada' => '❌ RECHAZADA',
            default => strtoupper($estado)
        };
    }
    /**
     * Obtener cabecera según el tipo de ticket
     */
    protected function getCabeceraPorTipo(string $tipo): string
    {
        return match ($tipo) {
            'movimiento' => 'Comprobante de Movimiento de Caja',
            'transferencia' => 'Comprobante de Transferencia entre Cajas',
            'arqueo' => 'Reporte de Arqueo de Caja',
            'cierre' => 'Reporte de Cierre de Caja',
            default => 'Comprobante de Caja'
        };
    }
}