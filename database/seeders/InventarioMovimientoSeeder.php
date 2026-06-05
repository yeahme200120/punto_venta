<?php

namespace Database\Seeders;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Insumo;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventarioMovimientoSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = \App\Models\Empresa::all();

        foreach ($empresas as $empresa) {
            $usuarios = User::where('empresa_id', $empresa->id)->get();
            $productos = Producto::where('empresa_id', $empresa->id)->get();
            $insumos = Insumo::where('empresa_id', $empresa->id)->get();

            $tipos = ['entrada', 'salida', 'ajuste'];
            $motivos = ['compra', 'venta', 'devolucion', 'merma', 'ajuste_inventario'];

            if ($usuarios->isEmpty()) continue;

            $usuario = $usuarios->first();

            // Crear movimientos de productos
            foreach ($productos as $producto) {
                $tipo = $tipos[array_rand($tipos)];
                $motivo = $motivos[array_rand($motivos)];
                $cantidad = rand(1, 20);
                $costo = $producto->costo_compra;

                InventarioMovimiento::create([
                    'empresa_id' => $empresa->id,
                    'producto_id' => $producto->id,
                    'insumo_id' => null,
                    'user_id' => $usuario->id,
                    'tipo' => $tipo,
                    'motivo' => $motivo,
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costo,
                    'costo_total' => $costo * $cantidad,
                    'observacion' => "Movimiento inicial de {$producto->nombre}",
                    'referencia' => 'INICIAL-' . date('Ymd'),
                ]);
            }

            // Crear movimientos de insumos
            foreach ($insumos as $insumo) {
                $tipo = $tipos[array_rand($tipos)];
                $motivo = $motivos[array_rand($motivos)];
                $cantidad = rand(10, 100);
                $costo = $insumo->costo_unitario;

                InventarioMovimiento::create([
                    'empresa_id' => $empresa->id,
                    'producto_id' => null,
                    'insumo_id' => $insumo->id,
                    'user_id' => $usuario->id,
                    'tipo' => $tipo,
                    'motivo' => $motivo,
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costo,
                    'costo_total' => $costo * $cantidad,
                    'observacion' => "Movimiento inicial de {$insumo->nombre}",
                    'referencia' => 'INICIAL-' . date('Ymd'),
                ]);
            }
        }
    }
}