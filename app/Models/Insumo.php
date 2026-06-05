<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $table = 'insumos';
    protected $fillable = [
        'empresa_id',
        'proveedor_id',
        'codigo',
        'nombre',
        'descripcion',
        'unidad_medida',
        'unidad_medida_id',
        'costo_unitario',
        'stock',
        'stock_minimo',
        'stock_maximo',
        'activo',
        'ultima_notificacion_stock'
    ];
    protected $casts = [
        'costo_unitario' => 'decimal:2',
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
        'activo' => 'boolean',
        'ultima_notificacion_stock' => 'datetime'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_insumos', 'insumo_id', 'producto_id')
            ->withPivot('cantidad', 'activo')
            ->withTimestamps();
    }

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class);
    }
    // Generar código automático para insumos
     public static function generarCodigo()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, 4)) + 1 : 1;
        return 'INS-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
    // Accessors
    public function getStockBajoAttribute()
    {
        return $this->stock <= $this->stock_minimo;
    }
    
    public function getStockSobreAttribute()
    {
        return $this->stock >= $this->stock_maximo;
    }
    
    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock <= stock_minimo');
    }
}
