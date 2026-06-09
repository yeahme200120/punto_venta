<?php
// app/Exports/Sheets/InfoEmpresaSheet.php

namespace App\Exports\Sheets;

use App\Models\Empresa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class InfoEmpresaSheet implements FromCollection, WithTitle
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        $empresa = Empresa::find($this->empresaId);
        
        return collect([
            ['Información de la Empresa', ''],
            ['ID Empresa', $empresa->id ?? ''],
            ['Nombre', $empresa->nombre ?? ''],
            ['RFC', $empresa->rfc ?? ''],
            ['Dirección', $empresa->direccion ?? ''],
            ['Teléfono', $empresa->telefono ?? ''],
            ['Email', $empresa->correo ?? ''],
            ['Fecha de respaldo', now()->format('d/m/Y H:i:s')],
            ['', ''],
            ['NOTA:', 'Este respaldo contiene SOLO los datos de la empresa seleccionada'],
            ['Al importar, se respetará el ID de empresa actual']
        ]);
    }

    public function title(): string
    {
        return 'info_empresa';
    }
}