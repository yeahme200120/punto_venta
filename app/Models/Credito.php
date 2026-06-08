<?php
// app/Models/Credito.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    protected $table = 'creditos';
    
    protected $fillable = [
        'empresa_id', 'sucursal_id', 'venta_id', 'cliente_id', 'user_id',
        'monto_total', 'monto_pagado', 'saldo_pendiente', 'plazo', 'num_pagos',
        'estado', 'fecha_inicio', 'fecha_fin'
    ];
    
    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'num_pagos' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function pagares()
    {
        return $this->hasMany(Pagare::class);
    }
    
    public function cobranzas()
    {
        return $this->hasMany(Cobranza::class);
    }
    
    public static function getPlazos()
    {
        return [
            '7_dias' => '7 días',
            '15_dias' => '15 días',
            '1_mes' => '1 mes',
            '2_meses' => '2 meses',
            '3_meses' => '3 meses',
            '6_meses' => '6 meses',
            '1_ano' => '1 año'
        ];
    }
}