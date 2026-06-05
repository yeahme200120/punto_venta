<?php
// app/Models/UnidadMedida.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidad_medidas';
    
    protected $fillable = [
        'tipo',
        'clave',
        'nombre',
        'descripcion',
        'simbolo',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean'
    ];
    
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'unidad_medida_id');
    }
    
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}