<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impresora extends Model
{
    protected $table = 'impresoras';
    protected $fillable = ['empresa_id', 'sucursal_id', 'nombre', 'tipo', 'puerto', 'ip', 'activo'];
    protected $casts = ['activo' => 'boolean'];
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
