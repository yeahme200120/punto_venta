<?php
// app/Models/Cotizacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizacions';
    
    protected $fillable = [
        'empresa_id', 'sucursal_id', 'user_id', 'cliente_id',
        'folio', 'estado', 'subtotal', 'iva', 'total', 'observaciones', 
        'fecha_validez', 'fecha_cotizacion'
    ];
    
    protected $casts = [
        'fecha_validez' => 'date',
        'fecha_cotizacion' => 'datetime',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2'
    ];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function detalles()
    {
        return $this->hasMany(CotizacionDetalle::class);
    }
    
    public static function generarFolio()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->folio, 4)) + 1 : 1;
        return 'COT-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
    }
    
}