<?php
// app/Exports/Sheets/UnidadesMedidaSheet.php

namespace App\Exports\Sheets;

use App\Models\UnidadMedida;

class UnidadesMedidaSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'unidades_medida');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Clave', 'Nombre', 'Descripción', 'Símbolo', 'Activo'
        ];
    }

    public function query()
    {
        // Unidades de medida son globales (no tienen empresa_id)
        // Se exportan todas las activas
        return UnidadMedida::where('activo', true)
            ->select('id', 'clave', 'nombre', 'descripcion', 'simbolo', 'activo');
    }

    public function map($unidad): array
    {
        return [
            $unidad->id,
            $unidad->clave,
            $unidad->nombre,
            $unidad->descripcion,
            $unidad->simbolo,
            $unidad->activo ? 'Sí' : 'No'
        ];
    }
}