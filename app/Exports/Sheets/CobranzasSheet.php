<?php
// app/Exports/Sheets/CobranzasSheet.php

namespace App\Exports\Sheets;

use App\Models\Cobranza;

class CobranzasSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'cobranzas');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Crédito ID', 'Usuario', 'Monto', 'Tipo',
            'Observaciones', 'Fecha Cobro'
        ];
    }

    public function query()
    {
        return Cobranza::where('empresa_id', $this->empresaId)
            ->with('usuario')
            ->select('id', 'credito_id', 'user_id', 'monto', 'tipo', 'observaciones', 'fecha_cobro');
    }

    public function map($cobranza): array
    {
        return [
            $cobranza->id,
            $cobranza->credito_id,
            $cobranza->usuario->name ?? '',
            $cobranza->monto,
            $cobranza->tipo,
            $cobranza->observaciones,
            $cobranza->fecha_cobro->format('d/m/Y H:i')
        ];
    }
}