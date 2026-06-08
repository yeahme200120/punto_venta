<?php
// app/Models/Pagare.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagare extends Model
{
    protected $table = 'pagares';
    
    protected $fillable = [
        'credito_id', 'folio', 'numero_pago', 'monto', 'fecha_vencimiento', 
        'estado', 'fecha_pago'
    ];
    
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'datetime'
    ];
    
    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }
    
    public static function generarFolio()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->folio, 4)) + 1 : 1;
        return 'PAG-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
    }
}