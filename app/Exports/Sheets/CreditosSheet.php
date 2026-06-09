<?php
// app/Exports/Sheets/CreditosSheet.php

namespace App\Exports\Sheets;

use App\Models\Credito;

class CreditosSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'creditos');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Venta ID', 'Cliente', 'Monto Total', 'Monto Pagado',
            'Saldo Pendiente', 'Plazo', 'Número Pagos', 'Estado',
            'Fecha Inicio', 'Fecha Fin'
        ];
    }

    public function query()
    {
        return Credito::where('empresa_id', $this->empresaId)
            ->with('cliente', 'venta')
            ->select('id', 'venta_id', 'cliente_id', 'monto_total', 'monto_pagado',
                     'saldo_pendiente', 'plazo', 'num_pagos', 'estado',
                     'fecha_inicio', 'fecha_fin');
    }

    public function map($credito): array
    {
        return [
            $credito->id,
            $credito->venta_id,
            $credito->cliente->nombre ?? '',
            $credito->monto_total,
            $credito->monto_pagado,
            $credito->saldo_pendiente,
            $credito->plazo,
            $credito->num_pagos,
            $credito->estado,
            $credito->fecha_inicio->format('d/m/Y'),
            $credito->fecha_fin->format('d/m/Y')
        ];
    }
}