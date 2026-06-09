<?php
// app/Exports/Sheets/CotizacionesSheet.php

namespace App\Exports\Sheets;

use App\Models\Cotizacion;

class CotizacionesSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'cotizaciones');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Folio', 'Fecha Cotización', 'Cliente', 'Estado',
            'Subtotal', 'IVA', 'Total', 'Fecha Validez', 'Observaciones'
        ];
    }

    public function query()
    {
        return Cotizacion::where('empresa_id', $this->empresaId)
            ->with('cliente')
            ->select('id', 'folio', 'fecha_cotizacion', 'cliente_id', 'estado',
                     'subtotal', 'iva', 'total', 'fecha_validez', 'observaciones');
    }

    public function map($cotizacion): array
    {
        return [
            $cotizacion->id,
            $cotizacion->folio,
            $cotizacion->fecha_cotizacion->format('d/m/Y H:i'),
            $cotizacion->cliente->nombre ?? 'Mostrador',
            $cotizacion->estado,
            $cotizacion->subtotal,
            $cotizacion->iva,
            $cotizacion->total,
            $cotizacion->fecha_validez ? $cotizacion->fecha_validez->format('d/m/Y') : '',
            $cotizacion->observaciones
        ];
    }
}