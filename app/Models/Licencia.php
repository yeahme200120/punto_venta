<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Licencia extends Model
{
    protected $table = 'licencias';

    protected $fillable = [
        'nombre',
        'dias',
        'max_usuarios',
        'max_sucursales',
        'precio',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2'
    ];

    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'licencia_id');
    }
}
