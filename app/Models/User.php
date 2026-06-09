<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'name',
        'email',
        'password',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean'
        ];
    }
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
    // Verificar si la licencia de su empresa sigue vigente
    public function licenciaVigente(): bool
    {
        if (!$this->empresa)
            return false;
        if (!$this->empresa->activo)
            return false;
        return $this->empresa->fecha_fin >= now();
    }
    public function refreshPermissions()
    {
        $this->load('permissions', 'roles');
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return $this;
    }
}
