<?php
// app/Exports/Sheets/VentasSheet.php

namespace App\Exports\Sheets;

use App\Models\Venta;

class VentasSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'ventas');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Folio', 'Fecha Venta', 'Cliente', 'Tipo', 'Estado',
            'Subtotal', 'IVA', 'Total', 'Usuario'
        ];
    }

    public function query()
    {
        return Venta::where('empresa_id', $this->empresaId)
            ->with(['cliente', 'usuario'])
            ->select('id', 'folio', 'fecha_venta', 'cliente_id', 'tipo', 
                     'estado', 'subtotal', 'iva', 'total', 'user_id');
    }

    public function map($venta): array
    {
        return [
            $venta->id,
            $venta->folio,
            $venta->fecha_venta->format('d/m/Y H:i'),
            $venta->cliente->nombre ?? 'Mostrador',
            $venta->tipo,
            $venta->estado,
            $venta->subtotal,
            $venta->iva,
            $venta->total,
            $venta->usuario->name ?? ''
        ];
    }
}