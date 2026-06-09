<?php
// app/Exports/Sheets/CotizacionesDetallesSheet.php

namespace App\Exports\Sheets;

use App\Models\CotizacionDetalle;

class CotizacionesDetallesSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'cotizaciones_detalles');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Cotización ID', 'Producto', 'Cantidad', 'Precio Unitario', 'Subtotal'
        ];
    }

    public function query()
    {
        // Obtener IDs de cotizaciones de la empresa
        $cotizacionesIds = \App\Models\Cotizacion::where('empresa_id', $this->empresaId)->pluck('id');
        
        return CotizacionDetalle::whereIn('cotizacion_id', $cotizacionesIds)
            ->with('producto')
            ->select('id', 'cotizacion_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal');
    }

    public function map($detalle): array
    {
        return [
            $detalle->id,
            $detalle->cotizacion_id,
            $detalle->producto->nombre ?? 'Producto eliminado',
            $detalle->cantidad,
            $detalle->precio_unitario,
            $detalle->subtotal
        ];
    }
}