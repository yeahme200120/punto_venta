<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoRelacionado extends Model
{
    protected $table = 'producto_relacionados';
    
    protected $fillable = [
        'producto_id',
        'producto_relacionado_id',
        'orden',
        'activo'
    ];
    
    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean'
    ];
    
    // Relación con el producto principal
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    
    // Relación con el producto relacionado
    public function productoRelacionado()
    {
        return $this->belongsTo(Producto::class, 'producto_relacionado_id');
    }
    
    // Scope para activos
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    // Scope para ordenados
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }
}