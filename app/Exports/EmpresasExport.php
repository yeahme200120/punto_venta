<?php

namespace App\Exports;

use App\Models\Empresa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmpresasExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Empresa::with(['licencia', 'sucursales', 'usuarios'])
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'RFC',
            'Licencia',
            'Teléfono',
            'Correo',
            'Sucursales',
            'Usuarios',
            'Fecha Inicio',
            'Fecha Vencimiento',
            'Estado',
        ];
    }

    public function map($empresa): array
    {
        return [
            $empresa->nombre,
            $empresa->rfc,
            $empresa->licencia->nombre ?? 'N/A',
            $empresa->telefono ?? '—',
            $empresa->correo ?? '—',
            $empresa->sucursales->count(),
            $empresa->usuarios->count(),
            $empresa->fecha_inicio?->format('d/m/Y'),
            $empresa->fecha_fin?->format('d/m/Y'),
            $empresa->activo ? 'Activo' : 'Inactivo',
        ];
    }
}