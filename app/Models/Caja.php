<?php
// app/Models/Caja.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';
    
    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'nombre',
        'codigo',
        'descripcion',
        'saldo_inicial',
        'saldo_actual',
        'activo',
        'permite_multiple'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'permite_multiple' => 'boolean',
        'saldo_inicial' => 'decimal:2',
        'saldo_actual' => 'decimal:2'
    ];
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    
    public function aperturas()
    {
        return $this->hasMany(CajaApertura::class);
    }
    
    public function aperturaActual()
    {
        return $this->hasOne(CajaApertura::class)->where('estado', 'abierta');
    }
    
    public function tieneAperturaAbierta()
    {
        return $this->aperturas()->where('estado', 'abierta')->exists();
    }
    
    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }
    
    public static function generarCodigo()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, 4)) + 1 : 1;
        return 'CAJ-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}