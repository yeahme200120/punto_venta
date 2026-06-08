<?php
// app/Models/Carrito.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table = 'carritos';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'sucursal_id',
        'items',
        'subtotal',
        'iva',
        'total'
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public static function obtenerCarrito()
    {
        $user = auth()->user();

        return self::firstOrCreate(
            [
                'user_id' => $user->id,
                'empresa_id' => $user->empresa_id,
                'sucursal_id' => $user->sucursal_id
            ],
            [
                'items' => [], // Valor por defecto como JSON
                'subtotal' => 0,
                'iva' => 0,
                'total' => 0
            ]
        );
        // Asegurar que items sea siempre un array
        if (is_null($carrito->items) || is_string($carrito->items)) {
            $carrito->items = [];
            $carrito->save();
        }
        
        return $carrito;

    }

    public function actualizarTotales()
    {
        $subtotal = 0;
        $items = $this->items;

        // Validar que items sea array
        if (!is_array($items)) {
            $items = [];
        }

        foreach ($items as $item) {
            $precio = floatval($item['precio']);  // Convertir a número
            $cantidad = intval($item['cantidad']); // Convertir a entero
            $subtotal += $precio * $cantidad;
        }

        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        $this->update([
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total
        ]);

        return $this;
    }

    public function agregarItem($producto, $cantidad = 1)
    {
        $items = $this->items;
        // Validar que items sea array
        if (!is_array($items)) {
            $items = [];
        }

        $existe = false;

        foreach ($items as &$item) {
            if ($item['id'] == $producto->id) {
                $item['cantidad'] = intval($item['cantidad']) + intval($cantidad);
                $existe = true;
                break;
            }
        }

        if (!$existe) {
            $items[] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => floatval($producto->precio_venta),
                'cantidad' => intval($cantidad)
            ];
        }

        $this->items = $items;
        $this->save();

        return $this->actualizarTotales();
    }

    public function eliminarItem($index)
    {
        $items = $this->items;
        
        // Validar que items sea array
        if (!is_array($items)) {
            $items = [];
        }
        
        if (isset($items[$index])) {
            array_splice($items, $index, 1);
            $this->items = $items;
            $this->save();
        }

        return $this->actualizarTotales();
    }

    public function actualizarCantidad($index, $cantidad)
    {
         $items = $this->items;
        
        // Validar que items sea array
        if (!is_array($items)) {
            $items = [];
        }
        
        if (isset($items[$index])) {
            $items[$index]['cantidad'] = intval($cantidad);
            $this->items = $items;
            $this->save();
        }

        return $this->actualizarTotales();
    }

    public function limpiar()
    {
        $this->items = [];
        $this->subtotal = 0;
        $this->iva = 0;
        $this->total = 0;
        $this->save();

        return $this;
    }
}