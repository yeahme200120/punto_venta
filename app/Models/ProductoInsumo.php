<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoInsumo extends Model
{
    protected $table = 'producto_insumos'; // Especificar el nombre correcto de la tabla
    
    protected $fillable = [
        'producto_id',
        'insumo_id',
        'cantidad',
        'activo'
    ];
    
    protected $casts = [
        'cantidad' => 'decimal:4',
        'activo' => 'boolean'
    ];
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
