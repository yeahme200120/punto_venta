<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
     protected $table = 'sucursals';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'direccion',
        'telefono',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }
}
