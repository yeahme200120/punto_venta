<?php
// app/Exports/Sheets/CategoriasSheet.php

namespace App\Exports\Sheets;

use App\Models\Categoria;

class CategoriasSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'categorias');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Nombre', 'Descripción', 'Activo'
        ];
    }

    public function query()
    {
        return Categoria::where('empresa_id', $this->empresaId)
            ->select('id', 'nombre', 'descripcion', 'activo');
    }

    public function map($categoria): array
    {
        return [
            $categoria->id,
            $categoria->nombre,
            $categoria->descripcion,
            $categoria->activo ? 'Sí' : 'No'
        ];
    }
}