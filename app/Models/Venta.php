<?php
// app/Models/Venta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'caja_apertura_id',
        'user_id',
        'cliente_id',
        'folio',
        'tipo',
        'estado',
        'subtotal',
        'iva',
        'total',
        'observaciones',
        'fecha_venta'
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
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

    public function cajaApertura()
    {
        return $this->belongsTo(CajaApertura::class);
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
        return $this->hasMany(VentaDetalle::class);
    }

    public function credito()
    {
        return $this->hasOne(Credito::class);
    }

    public static function generarFolio()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->folio, 4)) + 1 : 1;
        return 'VEN-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
    }
    // app/Models/Venta.php - agregar relación

    public function pagoDetalles()
    {
        return $this->hasMany(PagoDetalle::class);
    }
}