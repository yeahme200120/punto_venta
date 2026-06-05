<?php

namespace App\Exports;

use App\Models\Proveedor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProveedoresExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return Proveedor::where('empresa_id', $this->empresaId)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'RFC',
            'Teléfono',
            'Correo',
            'Dirección',
            'Estado',
            'Fecha Registro',
        ];
    }

    public function map($proveedor): array
    {
        return [
            $proveedor->nombre,
            $proveedor->rfc ?? '—',
            $proveedor->telefono ?? '—',
            $proveedor->correo ?? '—',
            $proveedor->direccion ?? '—',
            $proveedor->activo ? 'Activo' : 'Inactivo',
            $proveedor->created_at->format('d/m/Y'),
        ];
    }
}