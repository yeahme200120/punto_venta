<?php
// app/Exports/Sheets/ProductosSheet.php

namespace App\Exports\Sheets;

use App\Models\Producto;

class ProductosSheet extends BaseSheet
{
    public function __construct($empresaId)
    {
        parent::__construct($empresaId, 'productos');
    }

    protected function getHeadings(): array
    {
        return [
            'ID', 'Código Barras', 'SKU', 'Nombre', 'Descripción', 
            'Costo Compra', 'Precio Venta', 'Stock', 'Stock Mínimo', 
            'Stock Máximo', 'Categoría', 'Control Inventario', 'Activo'
        ];
    }

    public function query()
    {
        return Producto::where('empresa_id', $this->empresaId)
            ->with('categoria')
            ->select('id', 'codigo_barras', 'sku', 'nombre', 'descripcion', 
                     'costo_compra', 'precio_venta', 'stock', 'stock_minimo', 
                     'stock_maximo', 'categoria_id', 'control_inventario', 'activo');
    }

    public function map($producto): array
    {
        return [
            $producto->id,
            $producto->codigo_barras,
            $producto->sku,
            $producto->nombre,
            $producto->descripcion,
            $producto->costo_compra,
            $producto->precio_venta,
            $producto->stock,
            $producto->stock_minimo,
            $producto->stock_maximo,
            $producto->categoria->nombre ?? '',
            $producto->control_inventario ? 'Sí' : 'No',
            $producto->activo ? 'Sí' : 'No'
        ];
    }
}