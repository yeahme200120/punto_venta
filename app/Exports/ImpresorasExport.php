<?php

namespace App\Exports;

use App\Models\Impresora;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ImpresorasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return Impresora::with('sucursal')
            ->where('empresa_id', $this->empresaId)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Tipo',
            'Sucursal',
            'Puerto',
            'IP',
            'Estado',
            'Fecha Registro',
        ];
    }

    public function map($impresora): array
    {
        return [
            $impresora->nombre,
            ucfirst($impresora->tipo),
            $impresora->sucursal->nombre ?? 'Todas',
            $impresora->puerto ?? '—',
            $impresora->ip ?? '—',
            $impresora->activo ? 'Activo' : 'Inactivo',
            $impresora->created_at->format('d/m/Y'),
        ];
    }
}