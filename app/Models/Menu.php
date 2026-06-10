<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'modulo_id',
        'menu_padre_id',
        'nombre',
        'icono',
        'ruta',
        'orden',
        'permiso',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function padre()
    {
        return $this->belongsTo(Menu::class, 'menu_padre_id');
    }

    public function hijos()
    {
        return $this->hasMany(Menu::class, 'menu_padre_id');
    }
    /**
     * Obtener los hijos que están activos
     */
    public function hijosActivos()
    {
        return $this->hijos()->where('activo', true);
    }
}
