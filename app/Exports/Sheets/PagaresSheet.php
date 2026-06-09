<?php
// app/Exports/Sheets/PagaresSheet.php

namespace App\Exports\Sheets;

use App\Models\Pagare;

class PagaresSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'pagares');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Crédito ID', 'Folio', 'Número Pago', 'Monto',
            'Fecha Vencimiento', 'Estado', 'Fecha Pago'
        ];
    }

    public function query()
    {
        // Obtener IDs de créditos de la empresa
        $creditosIds = \App\Models\Credito::where('empresa_id', $this->empresaId)->pluck('id');
        
        return Pagare::whereIn('credito_id', $creditosIds)
            ->select('id', 'credito_id', 'folio', 'numero_pago', 'monto',
                     'fecha_vencimiento', 'estado', 'fecha_pago');
    }

    public function map($pagare): array
    {
        return [
            $pagare->id,
            $pagare->credito_id,
            $pagare->folio,
            $pagare->numero_pago,
            $pagare->monto,
            $pagare->fecha_vencimiento->format('d/m/Y'),
            $pagare->estado,
            $pagare->fecha_pago ? $pagare->fecha_pago->format('d/m/Y H:i') : ''
        ];
    }
}