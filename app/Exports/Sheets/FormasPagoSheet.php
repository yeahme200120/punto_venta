<?php
// app/Exports/Sheets/FormasPagoSheet.php

namespace App\Exports\Sheets;

use App\Models\FormaPago;

class FormasPagoSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'formas_pago');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Clave', 'Nombre', 'Icono', 'Orden', 'Activo',
            'Requiere Referencia', 'Requiere Autorización'
        ];
    }

    public function query()
    {
        return FormaPago::where('empresa_id', $this->empresaId)
            ->select('id', 'clave', 'nombre', 'icono', 'orden', 'activo',
                     'requiere_referencia', 'requiere_autorizacion');
    }

    public function map($forma): array
    {
        return [
            $forma->id,
            $forma->clave,
            $forma->nombre,
            $forma->icono,
            $forma->orden,
            $forma->activo ? 'Sí' : 'No',
            $forma->requiere_referencia ? 'Sí' : 'No',
            $forma->requiere_autorizacion ? 'Sí' : 'No'
        ];
    }
}