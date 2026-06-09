<?php
// app/Exports/Sheets/CajasSheet.php

namespace App\Exports\Sheets;

use App\Models\Caja;

class CajasSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'cajas');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Código', 'Nombre', 'Descripción', 'Sucursal',
            'Saldo Inicial', 'Saldo Actual', 'Permite Múltiple', 'Activo'
        ];
    }

    public function query()
    {
        return Caja::where('empresa_id', $this->empresaId)
            ->with('sucursal')
            ->select('id', 'codigo', 'nombre', 'descripcion', 'sucursal_id',
                     'saldo_inicial', 'saldo_actual', 'permite_multiple', 'activo');
    }

    public function map($caja): array
    {
        return [
            $caja->id,
            $caja->codigo,
            $caja->nombre,
            $caja->descripcion,
            $caja->sucursal->nombre ?? '',
            $caja->saldo_inicial,
            $caja->saldo_actual,
            $caja->permite_multiple ? 'Sí' : 'No',
            $caja->activo ? 'Sí' : 'No'
        ];
    }
}