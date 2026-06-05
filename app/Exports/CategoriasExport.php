<?php

namespace App\Exports;

use App\Models\Categoria;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return Categoria::withCount('productos')
            ->where('empresa_id', $this->empresaId)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripción',
            'Total Productos',
            'Estado',
            'Fecha Registro',
        ];
    }

    public function map($categoria): array
    {
        return [
            $categoria->nombre,
            $categoria->descripcion ?? '—',
            $categoria->productos_count,
            $categoria->activo ? 'Activo' : 'Inactivo',
            $categoria->created_at->format('d/m/Y'),
        ];
    }
}