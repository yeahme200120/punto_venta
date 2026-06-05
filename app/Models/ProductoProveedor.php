<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoProveedor extends Model
{
    protected $table = 'producto_proveedors'; // Especificar el nombre correcto de la tabla
    
    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'precio_compra',
        'tiempo_entrega_dias',
        'activo'
    ];
    
    protected $casts = [
        'precio_compra' => 'decimal:2',
        'tiempo_entrega_dias' => 'integer',
        'activo' => 'boolean'
    ];
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
