<?php
// app/Exports/Sheets/VentasDetallesSheet.php

namespace App\Exports\Sheets;

use App\Models\VentaDetalle;

class VentasDetallesSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'ventas_detalles');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Venta ID', 'Producto', 'Cantidad', 'Precio Unitario', 'Subtotal'
        ];
    }

    public function query()
    {
        // Obtener IDs de ventas de la empresa
        $ventasIds = \App\Models\Venta::where('empresa_id', $this->empresaId)->pluck('id');
        
        return VentaDetalle::whereIn('venta_id', $ventasIds)
            ->with('producto')
            ->select('id', 'venta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal');
    }

    public function map($detalle): array
    {
        return [
            $detalle->id,
            $detalle->venta_id,
            $detalle->producto->nombre ?? 'Producto eliminado',
            $detalle->cantidad,
            $detalle->precio_unitario,
            $detalle->subtotal
        ];
    }
}