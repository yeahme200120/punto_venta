<?php
// app/Models/PagoDetalle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoDetalle extends Model
{
    protected $table = 'pago_detalles';
    
    protected $fillable = [
        'venta_id', 'forma_pago_id', 'monto', 'referencia'
    ];
    
    protected $casts = [
        'monto' => 'decimal:2'
    ];
    
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    
    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class);
    }
}