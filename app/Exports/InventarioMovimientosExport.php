<?php

namespace App\Exports;

use App\Models\InventarioMovimiento;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventarioMovimientosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return InventarioMovimiento::with(['producto', 'insumo', 'usuario', 'sucursalOrigen', 'sucursalDestino'])
            ->where('empresa_id', $this->empresaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo',
            'Motivo',
            'Producto/Insumo',
            'Sucursal Origen',
            'Sucursal Destino',
            'Cantidad',
            'Costo Unitario',
            'Costo Total',
            'Usuario',
            'Observación',
            'Referencia',
        ];
    }

    public function map($movimiento): array
    {
        $item = $movimiento->producto 
            ? '📦 ' . $movimiento->producto->nombre 
            : ($movimiento->insumo ? '🧱 ' . $movimiento->insumo->nombre : '—');

        return [
            $movimiento->created_at->format('d/m/Y H:i'),
            ucfirst($movimiento->tipo),
            ucfirst(str_replace('_', ' ', $movimiento->motivo)),
            $item,
            $movimiento->sucursalOrigen->nombre ?? '—',
            $movimiento->sucursalDestino->nombre ?? '—',
            $movimiento->cantidad,
            '$' . number_format($movimiento->costo_unitario, 2),
            '$' . number_format($movimiento->costo_total, 2),
            $movimiento->usuario->name ?? '—',
            $movimiento->observacion ?? '—',
            $movimiento->referencia ?? '—',
        ];
    }
}