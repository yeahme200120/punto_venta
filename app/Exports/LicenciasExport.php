<?php

namespace App\Exports;

use App\Models\Licencia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LicenciasExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Licencia::withCount('empresas')
            ->orderBy('dias')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Días',
            'Max Usuarios',
            'Max Sucursales',
            'Precio',
            'Empresas Activas',
            'Estado',
            'Creado',
        ];
    }

    public function map($licencia): array
    {
        return [
            $licencia->nombre,
            $licencia->dias,
            $licencia->max_usuarios,
            $licencia->max_sucursales,
            '$' . number_format($licencia->precio, 2),
            $licencia->empresas_count,
            $licencia->activo ? 'Activo' : 'Inactivo',
            $licencia->created_at->format('d/m/Y'),
        ];
    }
}