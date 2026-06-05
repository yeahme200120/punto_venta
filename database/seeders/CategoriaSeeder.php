<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = \App\Models\Empresa::all();

        if ($empresas->isEmpty()) {
            $this->command->warn('No hay empresas, ejecuta EmpresaSeeder primero');
            return;
        }

        foreach ($empresas as $empresa) {
            $categorias = [
                ['nombre' => 'Electrónicos', 'descripcion' => 'Productos electrónicos y tecnológicos'],
                ['nombre' => 'Ropa', 'descripcion' => 'Prendas de vestir y accesorios'],
                ['nombre' => 'Alimentos', 'descripcion' => 'Productos alimenticios y bebidas'],
                ['nombre' => 'Herramientas', 'descripcion' => 'Herramientas y equipos'],
                ['nombre' => 'Muebles', 'descripcion' => 'Muebles y decoración'],
                ['nombre' => 'Papelería', 'descripcion' => 'Artículos de oficina y papelería'],
                ['nombre' => 'Juguetes', 'descripcion' => 'Juguetes y juegos'],
                ['nombre' => 'Deportes', 'descripcion' => 'Artículos deportivos'],
            ];

            foreach ($categorias as $categoria) {
                Categoria::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'nombre' => $categoria['nombre']
                    ],
                    [
                        'descripcion' => $categoria['descripcion'],
                        'activo' => true,
                    ]
                );
            }
            
            $this->command->info("Categorías creadas para empresa: {$empresa->nombre}");
        }
    }
}