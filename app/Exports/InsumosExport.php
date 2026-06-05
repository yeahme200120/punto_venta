<?php

namespace App\Exports;

use App\Models\Insumo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InsumosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return Insumo::with(['proveedor', 'productos'])
            ->where('empresa_id', $this->empresaId)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Nombre',
            'Descripción',
            'Unidad Medida',
            'Proveedor',
            'Costo Unitario',
            'Stock',
            'Stock Mínimo',
            'Stock Máximo',
            'Productos Asociados',
            'Estado',
            'Fecha Registro',
        ];
    }

    public function map($insumo): array
    {
        return [
            $insumo->codigo ?? '—',
            $insumo->nombre,
            $insumo->descripcion ?? '—',
            $insumo->unidad_medida,
            $insumo->proveedor->nombre ?? 'Sin proveedor',
            '$' . number_format($insumo->costo_unitario, 2),
            $insumo->stock,
            $insumo->stock_minimo,
            $insumo->stock_maximo,
            $insumo->productos->pluck('nombre')->implode(', ') ?: 'Ninguno',
            $insumo->activo ? 'Activo' : 'Inactivo',
            $insumo->created_at->format('d/m/Y'),
        ];
    }
}