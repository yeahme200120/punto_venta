<?php
// app/Exports/Sheets/ClientesSheet.php

namespace App\Exports\Sheets;

use App\Models\Cliente;

class ClientesSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'clientes');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Nombre', 'RFC', 'Teléfono', 'Correo', 'Dirección', 
            'Tipo', 'Límite Crédito', 'Días Crédito', 'Activo'
        ];
    }

    public function query()
    {
        return Cliente::where('empresa_id', $this->empresaId)
            ->select('id', 'nombre', 'rfc', 'telefono', 'correo', 'direccion', 
                     'tipo', 'limite_credito', 'dias_credito', 'activo');
    }

    public function map($cliente): array
    {
        return [
            $cliente->id,
            $cliente->nombre,
            $cliente->rfc,
            $cliente->telefono,
            $cliente->correo,
            $cliente->direccion,
            $cliente->tipo,
            $cliente->limite_credito,
            $cliente->dias_credito,
            $cliente->activo ? 'Sí' : 'No'
        ];
    }
}