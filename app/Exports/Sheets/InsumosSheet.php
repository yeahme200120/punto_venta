<?php
// app/Exports/Sheets/InsumosSheet.php

namespace App\Exports\Sheets;

use App\Models\Insumo;

class InsumosSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'insumos');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Código', 'Nombre', 'Descripción', 'Unidad Medida',
            'Costo Unitario', 'Stock', 'Stock Mínimo', 'Stock Máximo',
            'Proveedor', 'Activo'
        ];
    }

    public function query()
    {
        return Insumo::where('empresa_id', $this->empresaId)
            ->with('proveedor', 'unidadMedida')
            ->select('id', 'codigo', 'nombre', 'descripcion', 'unidad_medida_id',
                     'costo_unitario', 'stock', 'stock_minimo', 'stock_maximo',
                     'proveedor_id', 'activo');
    }

    public function map($insumo): array
    {
        return [
            $insumo->id,
            $insumo->codigo,
            $insumo->nombre,
            $insumo->descripcion,
            $insumo->unidadMedida->nombre ?? '',
            $insumo->costo_unitario,
            $insumo->stock,
            $insumo->stock_minimo,
            $insumo->stock_maximo,
            $insumo->proveedor->nombre ?? '',
            $insumo->activo ? 'Sí' : 'No'
        ];
    }
}