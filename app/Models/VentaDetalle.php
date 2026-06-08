<?php
// app/Models/VentaDetalle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = 'venta_detalles';
    
    protected $fillable = [
        'venta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal'
    ];
    
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];
    
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}