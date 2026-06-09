<?php
// app/Exports/Sheets/SucursalesSheet.php

namespace App\Exports\Sheets;

use App\Models\Sucursal;

class SucursalesSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'sucursales');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Nombre', 'Dirección', 'Teléfono', 'Activo'
        ];
    }

    public function query()
    {
        return Sucursal::where('empresa_id', $this->empresaId)
            ->select('id', 'nombre', 'direccion', 'telefono', 'activo');
    }

    public function map($sucursal): array
    {
        return [
            $sucursal->id,
            $sucursal->nombre,
            $sucursal->direccion,
            $sucursal->telefono,
            $sucursal->activo ? 'Sí' : 'No'
        ];
    }
}