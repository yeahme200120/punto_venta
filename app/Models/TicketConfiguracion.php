<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TicketConfiguracion extends Model
{
    protected $fillable = [
        'empresa_id',
        'tipo',
        'nombre_empresa',
        'logo_url',
        'direccion',
        'telefono',
        'email',
        'rfc',
        'cabecera',
        'footer',
        'mostrar_logo',
        'mostrar_direccion',
        'mostrar_telefono',
        'mostrar_email',
        'mostrar_rfc',
        'ancho_papel',
        'fuente',
        'tamano_fuente',
        'regimen_fiscal',
        'uso_cfdi',
        'mostrar_regimen',
        'auto_imprimir',
        'facturar',
        'copias',
        'activo'
    ];

    protected $casts = [
        'mostrar_logo' => 'boolean',
        'mostrar_direccion' => 'boolean',
        'mostrar_telefono' => 'boolean',
        'mostrar_email' => 'boolean',
        'mostrar_rfc' => 'boolean',
        'mostrar_regimen' => 'boolean',
        'auto_imprimir' => 'boolean',
        'facturar' => 'boolean',
        'activo' => 'boolean',
        'copias' => 'integer',
        'tamano_fuente' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

     public static function obtener(int $empresaId, string $tipo)
    {
        return self::where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->where('activo', true)
            ->first();
    }

   // Accessor: devuelve la URL del logo (propio o de la empresa)
    public function getLogoUrlAttribute($value)
    {
        // Si hay logo propio y existe, usarlo
        if ($value && Storage::disk('public')->exists($value)) {
            return Storage::url($value);
        }
        // Si no, usar el logo de la empresa
        if ($this->empresa && $this->empresa->logo_url) {
            return $this->empresa->logo_url;
        }
         // Tercero, logo por defecto (ruta pública)
        return asset('logo/LogoAdmin.png');
    }
}