<?php
// app/Models/EmpresaLicenciaHistorial.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaLicenciaHistorial extends Model
{
    protected $table = 'empresa_licencia_historials';
    
    protected $fillable = [
        'empresa_id', 'licencia_id', 'fecha_inicio_original', 
        'fecha_inicio_periodo', 'fecha_fin_periodo', 
        'monto_pagado', 'referencia_pago', 'observaciones'
    ];
    
    protected $casts = [
        'fecha_inicio_original' => 'date',
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'monto_pagado' => 'decimal:2'
    ];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    public function licencia()
    {
        return $this->belongsTo(Licencia::class);
    }
}