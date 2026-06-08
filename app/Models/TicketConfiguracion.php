<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Scope para obtener configuración activa por tipo
     */
    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Obtener configuración por empresa y tipo
     */
    public static function obtener(int $empresaId, string $tipo)
    {
        return self::where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->where('activo', true)
            ->first();
    }
}