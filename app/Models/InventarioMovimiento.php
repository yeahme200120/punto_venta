<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';
    protected $fillable = [
        'empresa_id',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'producto_id',
        'insumo_id',
        'user_id',
        'tipo',
        'motivo',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'observacion',
        'referencia'
    ];
    protected $casts = [
        'cantidad' => 'decimal:2',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }
    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
