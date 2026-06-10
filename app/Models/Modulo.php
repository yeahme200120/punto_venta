<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'nombre',
        'icono',
        'orden',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'modulo_id');
    }
    /**
     * Menús principales (sin padre)
     */
    public function menusPrincipales()
    {
        return $this->hasMany(Menu::class)->whereNull('menu_padre_id')->orderBy('orden');
    }
}
