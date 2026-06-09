<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'licencia_id',
        'nombre',
        'rfc',
        'direccion',
        'telefono',
        'correo',
        'logo',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    public function licencia()
    {
        return $this->belongsTo(Licencia::class, 'licencia_id');
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'empresa_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }
    // Verificar si la licencia está vigente
    public function licenciaVigente(): bool
    {
        return $this->activo && $this->fecha_fin >= now();
    }
    // Accessor para obtener la URL completa del logo
    public function getLogoUrlAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return Storage::url($this->logo);
        }
        return null;
    }

    // Método para eliminar el logo del almacenamiento
    public function deleteLogo()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            Storage::disk('public')->delete($this->logo);
        }
        $this->logo = null;
        $this->save();
    }
    protected static function booted()
    {
        static::deleting(function ($empresa) {
            $empresa->deleteLogo();
        });
    }
}
