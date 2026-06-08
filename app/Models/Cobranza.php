<?php
// app/Models/Cobranza.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cobranza extends Model
{
    protected $table = 'cobranzas';
    
    protected $fillable = [
        'empresa_id', 'sucursal_id', 'credito_id', 'pagare_id', 'user_id',
        'caja_movimiento_id', 'monto', 'tipo', 'observaciones', 'fecha_cobro'
    ];
    
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_cobro' => 'datetime'
    ];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    
    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }
    
    public function pagare()
    {
        return $this->belongsTo(Pagare::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function cajaMovimiento()
    {
        return $this->belongsTo(CajaMovimiento::class);
    }
}