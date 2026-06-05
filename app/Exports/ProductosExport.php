<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProductosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $empresaId;

    public function __construct($empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function collection()
    {
        return Producto::with(['categoria', 'insumos', 'proveedores'])
            ->where('empresa_id', $this->empresaId)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Código Barras',
            'Nombre',
            'Descripción',
            'Categoría',
            'Costo Compra',
            'Costo Venta',
            'Precio Venta',
            'Stock',
            'Stock Mínimo',
            'Stock Máximo',
            'Control Inventario',
            'Insumos',
            'Proveedores',
            'Estado',
            'Fecha Registro',
            'Última Actualización',
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->sku ?? '—',
            $producto->codigo_barras ?? '—',
            $producto->nombre,
            $producto->descripcion ?? '—',
            $producto->categoria->nombre ?? 'Sin categoría',
            '$' . number_format($producto->costo_compra, 2),
            '$' . number_format($producto->costo_venta, 2),
            '$' . number_format($producto->precio_venta, 2),
            $producto->stock,
            $producto->stock_minimo,
            $producto->stock_maximo,
            $producto->control_inventario ? 'Sí' : 'No',
            $producto->insumos->pluck('nombre')->implode(', ') ?: 'Ninguno',
            $producto->proveedores->pluck('nombre')->implode(', ') ?: 'Ninguno',
            $producto->activo ? 'Activo' : 'Inactivo',
            $producto->created_at->format('d/m/Y'),
            $producto->updated_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_WHITE]], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => '4F46E5']]],
        ];
    }
}