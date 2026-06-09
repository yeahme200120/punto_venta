<?php
// app/Exports/Sheets/InventarioMovimientosSheet.php

namespace App\Exports\Sheets;

use App\Models\InventarioMovimiento;

class InventarioMovimientosSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'inventario_movimientos');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Tipo', 'Motivo', 'Producto', 'Cantidad', 'Costo Unitario',
            'Costo Total', 'Observación', 'Usuario', 'Fecha'
        ];
    }

    public function query()
    {
        return InventarioMovimiento::where('empresa_id', $this->empresaId)
            ->with('producto', 'usuario')
            ->select('id', 'tipo', 'motivo', 'producto_id', 'cantidad',
                     'costo_unitario', 'costo_total', 'observacion', 'user_id', 'created_at');
    }

    public function map($movimiento): array
    {
        return [
            $movimiento->id,
            $movimiento->tipo,
            $movimiento->motivo,
            $movimiento->producto->nombre ?? 'Producto eliminado',
            $movimiento->cantidad,
            $movimiento->costo_unitario,
            $movimiento->costo_total,
            $movimiento->observacion,
            $movimiento->usuario->name ?? '',
            $movimiento->created_at->format('d/m/Y H:i')
        ];
    }
}