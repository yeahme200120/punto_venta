<?php

namespace App\Exports;

use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RolesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return Role::withCount(['users' => function ($query) {
            $query->where('empresa_id', $this->empresaId);
        }])
        ->with('permissions')
        ->orderBy('name')
        ->get();
    }

    public function headings(): array
    {
        return [
            'Rol',
            'Permisos Totales',
            'Usuarios en Empresa',
            'Permisos Asignados',
        ];
    }

    public function map($role): array
    {
        return [
            $role->name,
            $role->permissions->count(),
            $role->users_count,
            $role->permissions->pluck('name')->implode(', '),
        ];
    }
}