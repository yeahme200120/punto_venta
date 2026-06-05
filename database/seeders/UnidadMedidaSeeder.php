<?php
// database/seeders/UnidadMedidaSeeder.php
namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            // Múltiplos / Fracciones / Decimales
            ['tipo' => 'Múltiplos / Fracciones / Decimales', 'clave' => 'H87', 'nombre' => 'Pieza', 'simbolo' => 'pza', 'descripcion' => 'Unidad individual de producto'],
            
            // Unidades de venta
            ['tipo' => 'Unidades de venta', 'clave' => 'EA', 'nombre' => 'Elemento', 'simbolo' => 'el', 'descripcion' => 'Elemento individual'],
            ['tipo' => 'Unidades específicas de la industria', 'clave' => 'E48', 'nombre' => 'Unidad de Servicio', 'simbolo' => 'serv', 'descripcion' => 'Servicio prestado'],
            ['tipo' => 'Unidades de venta', 'clave' => 'ACT', 'nombre' => 'Actividad', 'simbolo' => 'act', 'descripcion' => 'Actividad realizada'],
            
            // Mecánica
            ['tipo' => 'Mecánica', 'clave' => 'KGM', 'nombre' => 'Kilogramo', 'simbolo' => 'kg', 'descripcion' => 'Unidad de masa'],
            ['tipo' => 'Unidades específicas de la industria', 'clave' => 'E51', 'nombre' => 'Trabajo', 'simbolo' => 'trab', 'descripcion' => 'Trabajo realizado'],
            
            // Diversos
            ['tipo' => 'Diversos', 'clave' => 'A9', 'nombre' => 'Tarifa', 'simbolo' => 'tar', 'descripcion' => 'Tarifa por servicio'],
            
            // Tiempo y Espacio
            ['tipo' => 'Tiempo y Espacio', 'clave' => 'MTR', 'nombre' => 'Metro', 'simbolo' => 'm', 'descripcion' => 'Unidad de longitud'],
            ['tipo' => 'Diversos', 'clave' => 'AB', 'nombre' => 'Paquete a granel', 'simbolo' => 'ab', 'descripcion' => 'Paquete sin empaque individual'],
            ['tipo' => 'Unidades específicas de la industria', 'clave' => 'BB', 'nombre' => 'Caja base', 'simbolo' => 'bb', 'descripcion' => 'Caja base de productos'],
            ['tipo' => 'Unidades de venta', 'clave' => 'KT', 'nombre' => 'Kit', 'simbolo' => 'kit', 'descripcion' => 'Conjunto de piezas'],
            ['tipo' => 'Unidades de venta', 'clave' => 'SET', 'nombre' => 'Conjunto', 'simbolo' => 'set', 'descripcion' => 'Conjunto de elementos'],
            ['tipo' => 'Tiempo y Espacio', 'clave' => 'LTR', 'nombre' => 'Litro', 'simbolo' => 'l', 'descripcion' => 'Unidad de volumen'],
            ['tipo' => 'Unidades de empaque', 'clave' => 'XBX', 'nombre' => 'Caja', 'simbolo' => 'caja', 'descripcion' => 'Caja de productos'],
            ['tipo' => 'Tiempo y Espacio', 'clave' => 'MON', 'nombre' => 'Mes', 'simbolo' => 'mes', 'descripcion' => 'Unidad de tiempo mensual'],
            ['tipo' => 'Tiempo y Espacio', 'clave' => 'HUR', 'nombre' => 'Hora', 'simbolo' => 'h', 'descripcion' => 'Unidad de tiempo'],
            ['tipo' => 'Tiempo y Espacio', 'clave' => 'MTK', 'nombre' => 'Metro cuadrado', 'simbolo' => 'm²', 'descripcion' => 'Unidad de superficie'],
            ['tipo' => 'Diversos', 'clave' => '11', 'nombre' => 'Equipos', 'simbolo' => 'eq', 'descripcion' => 'Equipo completo'],
            ['tipo' => 'Mecánica', 'clave' => 'MGM', 'nombre' => 'Miligramo', 'simbolo' => 'mg', 'descripcion' => 'Unidad de masa pequeña'],
            ['tipo' => 'Unidades de empaque', 'clave' => 'XPK', 'nombre' => 'Paquete', 'simbolo' => 'pqt', 'descripcion' => 'Paquete de productos'],
            ['tipo' => 'Unidades de empaque', 'clave' => 'XKI', 'nombre' => 'Kit', 'simbolo' => 'kit', 'descripcion' => 'Kit de piezas'],
            ['tipo' => 'Diversos', 'clave' => 'AS', 'nombre' => 'Variedad', 'simbolo' => 'var', 'descripcion' => 'Variedad de productos'],
            ['tipo' => 'Mecánica', 'clave' => 'GRM', 'nombre' => 'Gramo', 'simbolo' => 'g', 'descripcion' => 'Unidad de masa'],
            ['tipo' => 'Números enteros', 'clave' => 'PR', 'nombre' => 'Par', 'simbolo' => 'par', 'descripcion' => 'Par de elementos'],
            ['tipo' => 'Unidades de venta', 'clave' => 'DPC', 'nombre' => 'Docenas de piezas', 'simbolo' => 'doc', 'descripcion' => 'Docena de piezas'],
            ['tipo' => 'Unidades de empaque', 'clave' => 'XUN', 'nombre' => 'Unidad', 'simbolo' => 'u', 'descripcion' => 'Unidad individual'],
            ['tipo' => 'Tiempo y Espacio', 'clave' => 'DAY', 'nombre' => 'Día', 'simbolo' => 'd', 'descripcion' => 'Unidad de tiempo diaria'],
            ['tipo' => 'Unidades de empaque', 'clave' => 'XLT', 'nombre' => 'Lote', 'simbolo' => 'lote', 'descripcion' => 'Lote de productos'],
            ['tipo' => 'Diversos', 'clave' => '10', 'nombre' => 'Grupos', 'simbolo' => 'grp', 'descripcion' => 'Grupo de elementos'],
            ['tipo' => 'Tiempo y Espacio', 'clave' => 'MLT', 'nombre' => 'Mililitro', 'simbolo' => 'ml', 'descripcion' => 'Unidad de volumen pequeña'],
            ['tipo' => 'Unidades específicas de la industria', 'clave' => 'E54', 'nombre' => 'Viaje', 'simbolo' => 'viaje', 'descripcion' => 'Viaje realizado'],
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::firstOrCreate(
                ['clave' => $unidad['clave']],
                [
                    'tipo' => $unidad['tipo'],
                    'nombre' => $unidad['nombre'],
                    'simbolo' => $unidad['simbolo'],
                    'descripcion' => $unidad['descripcion'],
                    'activo' => true,
                ]
            );
        }
    }
}