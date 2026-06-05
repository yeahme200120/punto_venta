<?php
// app/Models/CajaTransferencia.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaTransferencia extends Model
{
    protected $table = 'caja_transferencias';
    
    protected $fillable = [
        'caja_origen_id',
        'caja_destino_id',
        'caja_apertura_origen_id',
        'caja_apertura_destino_id',
        'user_id',
        'autorizado_por',
        'monto',
        'motivo',
        'estado',
        'autorizado_en'
    ];
    
    protected $casts = [
        'monto' => 'decimal:2',
        'autorizado_en' => 'datetime'
    ];
    
    public function cajaOrigen()
    {
        return $this->belongsTo(Caja::class, 'caja_origen_id');
    }
    
    public function cajaDestino()
    {
        return $this->belongsTo(Caja::class, 'caja_destino_id');
    }
    
    public function aperturaOrigen()
    {
        return $this->belongsTo(CajaApertura::class, 'caja_apertura_origen_id');
    }
    
    public function aperturaDestino()
    {
        return $this->belongsTo(CajaApertura::class, 'caja_apertura_destino_id');
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function autorizador()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }
}