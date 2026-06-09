<?php
// app/Exports/Sheets/ProveedoresSheet.php

namespace App\Exports\Sheets;

use App\Models\Proveedor;

class ProveedoresSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'proveedores');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Nombre', 'RFC', 'Teléfono', 'Correo', 'Dirección', 'Activo'
        ];
    }

    public function query()
    {
        return Proveedor::where('empresa_id', $this->empresaId)
            ->select('id', 'nombre', 'rfc', 'telefono', 'correo', 'direccion', 'activo');
    }

    public function map($proveedor): array
    {
        return [
            $proveedor->id,
            $proveedor->nombre,
            $proveedor->rfc,
            $proveedor->telefono,
            $proveedor->correo,
            $proveedor->direccion,
            $proveedor->activo ? 'Sí' : 'No'
        ];
    }
}