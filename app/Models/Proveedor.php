<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedors';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'rfc',
        'telefono',
        'correo',
        'direccion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }
    public function getInicialesAttribute()
    {
        $palabras = explode(' ', $this->nombre);
        $iniciales = '';
        foreach ($palabras as $palabra) {
            if (!empty($palabra)) {
                $iniciales .= strtoupper($palabra[0]);
            }
        }
        return substr($iniciales, 0, 2);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function productos()
    {
        // Especificar el nombre correcto de la tabla: 'producto_proveedors'
        return $this->belongsToMany(Producto::class, 'producto_proveedors', 'proveedor_id', 'producto_id')
            ->withPivot('precio_compra', 'tiempo_entrega_dias', 'activo')
            ->withTimestamps();
    }

    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }

    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('rfc', 'LIKE', "%{$termino}%")
              ->orWhere('telefono', 'LIKE', "%{$termino}%")
              ->orWhere('correo', 'LIKE', "%{$termino}%");
        });
    }
}