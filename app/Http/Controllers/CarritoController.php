<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Cotizacion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    public function obtener()
    {
        try {
            $carrito = Carrito::obtenerCarrito();
            
            return response()->json([
                'success' => true,
                'items' => $carrito->items ?? [],
                'subtotal' => $carrito->subtotal,
                'iva' => $carrito->iva,
                'total' => $carrito->total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function agregar(Request $request)
    {
        try {
            $request->validate([
                'producto_id' => 'required|exists:productos,id',
                'cantidad' => 'required|integer|min:1'
            ]);
            
            $producto = Producto::findOrFail($request->producto_id);
            $carrito = Carrito::obtenerCarrito();
            
            $carrito->agregarItem($producto, $request->cantidad);
            
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'items' => $carrito->items,
                'subtotal' => $carrito->subtotal,
                'iva' => $carrito->iva,
                'total' => $carrito->total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function eliminarItem($index)
    {
        try {
            $carrito = Carrito::obtenerCarrito();
            $carrito->eliminarItem($index);
            
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado',
                'items' => $carrito->items,
                'subtotal' => $carrito->subtotal,
                'iva' => $carrito->iva,
                'total' => $carrito->total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function actualizarCantidad(Request $request, $index)
    {
        try {
            $request->validate(['cantidad' => 'required|integer|min:1']);
            
            $carrito = Carrito::obtenerCarrito();
            $carrito->actualizarCantidad($index, $request->cantidad);
            
            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada',
                'items' => $carrito->items,
                'subtotal' => $carrito->subtotal,
                'iva' => $carrito->iva,
                'total' => $carrito->total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function limpiar()
    {
        try {
            $carrito = Carrito::obtenerCarrito();
            $carrito->limpiar();
            
            return response()->json([
                'success' => true,
                'message' => 'Carrito limpiado',
                'items' => [],
                'subtotal' => 0,
                'iva' => 0,
                'total' => 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Cargar cotización al carrito
    public function cargarCotizacion($cotizacionId)
    {
        try {
            $cotizacion = Cotizacion::with('detalles.producto')->findOrFail($cotizacionId);
            $carrito = Carrito::obtenerCarrito();
            
            // Limpiar carrito actual
            $carrito->limpiar();
            
            // Agregar productos de la cotización
            foreach ($cotizacion->detalles as $detalle) {
                $producto = $detalle->producto;
                $carrito->agregarItem($producto, $detalle->cantidad);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Cotización cargada al carrito',
                'items' => $carrito->items,
                'subtotal' => $carrito->subtotal,
                'iva' => $carrito->iva,
                'total' => $carrito->total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}