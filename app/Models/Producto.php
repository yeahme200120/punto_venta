<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'codigo_barras',
        'sku',
        'nombre',
        'descripcion',
        'costo_compra',
        'precio_venta',
        'stock',
        'stock_minimo',
        'stock_maximo',
        'control_inventario',
        'activo',
        'ultima_notificacion_stock'
    ];

    protected $casts = [
        'costo_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
        'control_inventario' => 'boolean',
        'activo' => 'boolean',
        'ultima_notificacion_stock' => 'datetime'
    ];

    protected $appends = [
        'imagen_principal',
        'imagenes_urls',
        'stock_bajo'
    ];

    // ==================== RELACIONES ====================

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'producto_insumos', 'producto_id', 'insumo_id')
            ->withPivot('cantidad', 'activo')
            ->withTimestamps();
    }

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'producto_proveedors', 'producto_id', 'proveedor_id')
            ->withPivot('precio_compra', 'tiempo_entrega_dias', 'activo')
            ->withTimestamps();
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class);
    }

    // Productos relacionados (activos)
    public function relacionados(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_relacionados', 'producto_id', 'producto_relacionado_id')
            ->withPivot('orden', 'activo')
            ->wherePivot('activo', true)
            ->orderBy('pivot_orden')
            ->withTimestamps();
    }

    // Productos que tienen este como relacionado
    public function relacionadoCon(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_relacionados', 'producto_relacionado_id', 'producto_id')
            ->withPivot('orden', 'activo')
            ->wherePivot('activo', true)
            ->withTimestamps();
    }

    // Todos los productos relacionados (incluyendo inactivos)
    public function todosRelacionados(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_relacionados', 'producto_id', 'producto_relacionado_id')
            ->withPivot('orden', 'activo')
            ->withTimestamps();
    }

    // Imágenes del producto
    public function imagenes(): HasMany
    {
        return $this->hasMany(ProductoImagen::class)->orderBy('orden');
    }

    // ==================== ACCESORS ====================

    /**
     * Obtener la imagen principal del producto
     */
    public function getImagenPrincipalAttribute(): ?string
    {
        $principal = $this->imagenes()->where('principal', true)->first();
        if ($principal) {
            return Storage::url($principal->imagen);
        }

        $primera = $this->imagenes()->first();
        if ($primera) {
            return Storage::url($primera->imagen);
        }

        return null;
    }

    /**
     * Obtener todas las URLs de imágenes
     */
    public function getImagenesUrlsAttribute(): array
    {
        return $this->imagenes->map(function ($imagen) {
            return Storage::url($imagen->imagen);
        })->toArray();
    }

    /**
     * Verificar si el stock está bajo
     */
    public function getStockBajoAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    /**
     * Verificar si el stock está sobre el máximo
     */
    public function getStockSobreAttribute(): bool
    {
        return $this->stock >= $this->stock_maximo;
    }

    /**
     * Obtener margen de ganancia
     */
    public function getMargenGananciaAttribute(): float
    {
        if ($this->costo_compra > 0) {
            return (($this->precio_venta - $this->costo_compra) / $this->costo_compra) * 100;
        }
        return 0;
    }

    // ==================== VALIDACIONES ====================

    /**
     * Validar límite de productos relacionados
     */
    public function canAddRelacionado(): bool
    {
        return $this->relacionados()->count() < 3;
    }

    /**
     * Validar límite de imágenes
     */
    public function canAddImagen(): bool
    {
        return $this->imagenes()->count() < 3;
    }

    /**
     * Validar si el producto tiene stock suficiente
     */
    public function hasStock(float $cantidad): bool
    {
        return $this->stock >= $cantidad;
    }

    /**
     * Validar si el producto está activo
     */
    public function isActive(): bool
    {
        return $this->activo;
    }

    /**
     * Validar si controla inventario
     */
    public function controlsInventory(): bool
    {
        return $this->control_inventario;
    }

    // ==================== MUTATORS ====================

    /**
     * Establecer el nombre en mayúsculas
     */
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = ucfirst(strtolower(trim($value)));
    }

    /**
     * Establecer el SKU en mayúsculas
     */
    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = $value ? strtoupper(trim($value)) : null;
    }

    /**
     * Establecer el código de barras
     */
    public function setCodigoBarrasAttribute($value)
    {
        $this->attributes['codigo_barras'] = $value ? trim($value) : null;
    }

    // ==================== SCOPES ====================

    /**
     * Scope para productos activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para productos inactivos
     */
    public function scopeInactivo($query)
    {
        return $query->where('activo', false);
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock <= stock_minimo');
    }

    /**
     * Scope para productos con stock sobre el máximo
     */
    public function scopeStockSobre($query)
    {
        return $query->whereRaw('stock >= stock_maximo');
    }

    /**
     * Scope para filtrar por empresa
     */
    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * Scope para buscar por nombre, sku o código de barras
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
                ->orWhere('sku', 'LIKE', "%{$termino}%")
                ->orWhere('codigo_barras', 'LIKE', "%{$termino}%");
        });
    }

    // ==================== MÉTODOS PRÁCTICOS ====================

    /**
     * Sincronizar productos relacionados
     */
    public function syncRelacionados(array $relacionadosIds): void
    {
        // Limitar a máximo 3
        $relacionadosIds = array_slice($relacionadosIds, 0, 3);

        $data = [];
        foreach ($relacionadosIds as $orden => $id) {
            $data[$id] = ['orden' => $orden, 'activo' => true];
        }

        $this->todosRelacionados()->sync($data);
    }

    /**
     * Activar/Desactivar un producto relacionado
     */
    public function toggleRelacionadoActivo($relacionadoId): bool
    {
        $relacion = $this->todosRelacionados()->find($relacionadoId);
        if ($relacion) {
            $nuevoEstado = !$relacion->pivot->activo;
            $this->todosRelacionados()->updateExistingPivot($relacionadoId, [
                'activo' => $nuevoEstado
            ]);
            return $nuevoEstado;
        }
        return false;
    }

    /**
     * Reducir stock
     */
    public function reducirStock(float $cantidad, string $motivo, string $observacion = null): bool
    {
        if (!$this->hasStock($cantidad)) {
            return false;
        }

        $this->stock -= $cantidad;
        $this->save();

        InventarioMovimiento::create([
            'empresa_id' => $this->empresa_id,
            'producto_id' => $this->id,
            'user_id' => auth()->id(),
            'tipo' => 'salida',
            'motivo' => $motivo,
            'cantidad' => $cantidad,
            'costo_unitario' => $this->costo_compra,
            'costo_total' => $cantidad * $this->costo_compra,
            'observacion' => $observacion,
        ]);

        return true;
    }

    /**
     * Aumentar stock
     */
    public function aumentarStock(float $cantidad, string $motivo, string $observacion = null): void
    {
        $this->stock += $cantidad;
        $this->save();

        InventarioMovimiento::create([
            'empresa_id' => $this->empresa_id,
            'producto_id' => $this->id,
            'user_id' => auth()->id(),
            'tipo' => 'entrada',
            'motivo' => $motivo,
            'cantidad' => $cantidad,
            'costo_unitario' => $this->costo_compra,
            'costo_total' => $cantidad * $this->costo_compra,
            'observacion' => $observacion,
        ]);
    }
    // Generar código de barras automático
    public static function generarCodigoBarras()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval($ultimo->codigo_barras) + 1 : 7500000000000;
        return str_pad($numero, 13, '0', STR_PAD_LEFT);
    }

    // Generar SKU (alfanumérico con formato PROD-00000)
    public static function generarSKU()
    {
        $ultimo = self::orderBy('id', 'desc')->first();

        if ($ultimo && $ultimo->sku) {
            // Extraer el número del último SKU
            $numero = intval(substr($ultimo->sku, 5)) + 1;
            $sku = 'PROD-' . str_pad($numero, 5, '0', STR_PAD_LEFT);

            // Verificar si el SKU ya existe (por si acaso)
            while (self::where('sku', $sku)->exists()) {
                $numero++;
                $sku = 'PROD-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
            }

            return $sku;
        }

        return 'PROD-00001';
    }
}