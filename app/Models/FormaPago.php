<?php
// app/Models/FormaPago.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    protected $table = 'forma_pagos';
    
    protected $fillable = [
        'empresa_id', 'clave', 'nombre', 'icono', 'orden', 'activo',
        'requiere_referencia', 'requiere_autorizacion'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'requiere_referencia' => 'boolean',
        'requiere_autorizacion' => 'boolean',
        'orden' => 'integer'
    ];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    public function pagoDetalles()
    {
        return $this->hasMany(PagoDetalle::class);
    }
    
    public static function getFormasPagoActivas($empresaId)
    {
        return self::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }
}