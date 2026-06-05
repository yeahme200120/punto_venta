<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Insumo;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        if ($empresas->isEmpty()) {
            $this->command->info('No hay empresas, ejecuta EmpresaSeeder primero');
            return;
        }

        foreach ($empresas as $empresa) {
            $categorias = Categoria::where('empresa_id', $empresa->id)->get();
            $proveedores = Proveedor::where('empresa_id', $empresa->id)->get();
            $insumos = Insumo::where('empresa_id', $empresa->id)->get();

            if ($categorias->isEmpty()) {
                $this->command->warn("No hay categorías para empresa {$empresa->id}, saltando...");
                continue;
            }

            if ($proveedores->isEmpty()) {
                $this->command->warn("No hay proveedores para empresa {$empresa->id}, saltando...");
                continue;
            }

            $productosData = [
                [
                    'codigo_barras' => '7501234567890',
                    'sku' => 'SKU-001',
                    'nombre' => 'Laptop Gamer',
                    'descripcion' => 'Laptop de alto rendimiento',
                    'costo_compra' => 15000.00,
                    'precio_venta' => 19999.99,
                ],
                [
                    'codigo_barras' => '7501234567891',
                    'sku' => 'SKU-002',
                    'nombre' => 'Mouse inalámbrico',
                    'descripcion' => 'Mouse ergonómico',
                    'costo_compra' => 250.00,
                    'precio_venta' => 399.99,
                ],
                [
                    'codigo_barras' => '7501234567892',
                    'sku' => 'SKU-003',
                    'nombre' => 'Camisa de vestir',
                    'descripcion' => 'Camisa formal hombre',
                    'costo_compra' => 300.00,
                    'precio_venta' => 499.99,
                ],
                [
                    'codigo_barras' => '7501234567893',
                    'sku' => 'SKU-004',
                    'nombre' => 'Pan de caja',
                    'descripcion' => 'Pan integral',
                    'costo_compra' => 25.00,
                    'precio_venta' => 39.99,
                ],
            ];

            $productosCreados = collect();

            foreach ($productosData as $index => $productoData) {
                $categoria = $categorias[$index % count($categorias)];
                $proveedor = $proveedores[$index % count($proveedores)];

                try {
                    $productoCreado = Producto::create([
                        'empresa_id' => $empresa->id,
                        'categoria_id' => $categoria->id,
                        'codigo_barras' => $productoData['codigo_barras'],
                        'sku' => $productoData['sku'],
                        'nombre' => $productoData['nombre'],
                        'descripcion' => $productoData['descripcion'],
                        'costo_compra' => $productoData['costo_compra'],
                        'precio_venta' => $productoData['precio_venta'],
                        'stock' => rand(10, 100),
                        'stock_minimo' => 5,
                        'stock_maximo' => 200,
                        'control_inventario' => true,
                        'activo' => true,
                    ]);

                    // Relacionar producto con proveedores
                    $productoCreado->proveedores()->attach($proveedor->id, [
                        'precio_compra' => $productoData['costo_compra'],
                        'tiempo_entrega_dias' => rand(1, 7),
                        'activo' => true,
                    ]);

                    // Relacionar producto con insumos (si hay insumos disponibles)
                    if ($insumos->count() > 0) {
                        $insumoAsignado = $insumos[$index % count($insumos)];
                        $productoCreado->insumos()->attach($insumoAsignado->id, [
                            'cantidad' => rand(1, 10),
                            'activo' => true,
                        ]);
                    }
                    
                    $productosCreados->push($productoCreado);
                    $this->command->info("Producto '{$productoData['nombre']}' creado para empresa {$empresa->id}");
                    
                } catch (\Exception $e) {
                    $this->command->error("Error al crear producto {$productoData['nombre']}: " . $e->getMessage());
                }
            }

            // Relacionar productos entre sí (máximo 3 por producto)
            foreach ($productosCreados as $producto) {
                $relacionadosIds = $productosCreados
                    ->filter(function($p) use ($producto) {
                        return $p->id !== $producto->id;
                    })
                    ->take(3)
                    ->pluck('id')
                    ->toArray();
                
                // Usar el método syncRelacionados que incluye el campo activo
                $producto->syncRelacionados($relacionadosIds);
            }
            
            $this->command->info("Total de productos creados para empresa {$empresa->nombre}: " . $productosCreados->count());
        }
    }
}