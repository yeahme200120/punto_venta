<?php
// app/Models/CajaMovimiento.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaMovimiento extends Model
{
    protected $table = 'caja_movimientos';
    
    protected $fillable = [
        'caja_apertura_id',
        'user_id',
        'sucursal_id',
        'tipo',
        'categoria',
        'forma_pago',
        'monto',
        'referencia',
        'concepto',
        'comprobante',
        'referencia_id',
        'referencia_type',
        'requiere_autorizacion',
        'autorizado_por',
        'autorizado_en'
    ];
    
    protected $casts = [
        'monto' => 'decimal:2',
        'requiere_autorizacion' => 'boolean',
        'autorizado_en' => 'datetime'
    ];
    
    public function cajaApertura()
    {
        return $this->belongsTo(CajaApertura::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function autorizador()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }
    
    public function referencia()
    {
        if ($this->referencia_type && $this->referencia_id) {
            return $this->belongsTo($this->referencia_type, 'referencia_id');
        }
        return null;
    }
    
    public function scopeIngresos($query)
    {
        return $query->where('tipo', 'ingreso');
    }
    
    public function scopeEgresos($query)
    {
        return $query->where('tipo', 'egreso');
    }
    
    public function scopePorFormaPago($query, $formaPago)
    {
        return $query->where('forma_pago', $formaPago);
    }
}