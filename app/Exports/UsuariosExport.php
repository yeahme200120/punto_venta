<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsuariosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $empresaId;
    protected $sucursalId;

     public function __construct($empresaId, $sucursalId = null)
    {
        $this->empresaId = $empresaId;
        $this->sucursalId = $sucursalId;
    }

    public function collection()
    {
        return User::with(['roles', 'sucursal', 'empresa'])
            ->where('empresa_id', $this->empresaId)
            ->when($this->sucursalId, function ($query) {
                return $query->where('sucursal_id', $this->sucursalId);
            })
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Email',
            'Empresa',
            'Sucursal',
            'Roles',
            'Estado',
            'Fecha Registro'
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->empresa->nombre ?? '—',
            $user->sucursal->nombre ?? 'Sin sucursal',
            $user->roles->pluck('name')->implode(', '),
            $user->activo ? 'Activo' : 'Inactivo',
            $user->created_at->format('d/m/Y H:i'),
        ];
    }
}