<?php

namespace App\Exports;

use App\Models\Cliente;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return Cliente::with('sucursal')
            ->where('empresa_id', $this->empresaId)
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
            'Tipo',
            'Límite Crédito',
            'Días Crédito',
            'Sucursal',
            'Estado',
            'Fecha Registro',
        ];
    }

    public function map($cliente): array
    {
        return [
            $cliente->nombre,
            $cliente->rfc ?? '—',
            $cliente->telefono ?? '—',
            $cliente->correo ?? '—',
            $cliente->direccion ?? '—',
            ucfirst($cliente->tipo),
            '$' . number_format($cliente->limite_credito, 2),
            $cliente->dias_credito,
            $cliente->sucursal->nombre ?? '—',
            $cliente->activo ? 'Activo' : 'Inactivo',
            $cliente->created_at->format('d/m/Y'),
        ];
    }
}