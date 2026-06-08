<?php
// database/seeders/CajaMovimientoSeeder.php

namespace Database\Seeders;

use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Models\User;
use Illuminate\Database\Seeder;

class CajaMovimientoSeeder extends Seeder
{
    private $categoriasIngreso = ['venta', 'abono_credito', 'cobro_servicio'];
    private $categoriasEgreso = ['compra', 'gasto', 'retiro'];
    private $formasPago = ['efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia'];
    private $conceptosIngreso = [
        'Venta de productos',
        'Venta mostrador',
        'Abono a cuenta',
        'Servicio de consultoría',
        'Pago de factura',
        'Venta online',
        'Venta por catálogo'
    ];
    private $conceptosEgreso = [
        'Compra de mercadería',
        'Pago a proveedor',
        'Gastos operativos',
        'Retiro de efectivo',
        'Pago de servicios',
        'Compra de insumos',
        'Mantenimiento'
    ];

    public function run(): void
    {
        $aperturas = CajaApertura::where('estado', 'cerrada')->get();
        $usuarios = User::all();
        
        foreach ($aperturas as $apertura) {
            $totalIngresos = 0;
            $totalEgresos = 0;
            
            // Generar entre 5 y 30 movimientos por día
            $numMovimientos = rand(5, 30);
            
            for ($i = 0; $i < $numMovimientos; $i++) {
                // 70% ingresos, 30% egresos
                $tipo = rand(1, 100) <= 70 ? 'ingreso' : 'egreso';
                
                $monto = $tipo == 'ingreso' 
                    ? rand(50, 5000) / 1.0  // Ingresos entre 50 y 5000
                    : rand(20, 2000) / 1.0;  // Egresos entre 20 y 2000
                
                $categoria = $tipo == 'ingreso' 
                    ? $this->categoriasIngreso[array_rand($this->categoriasIngreso)]
                    : $this->categoriasEgreso[array_rand($this->categoriasEgreso)];
                
                $formaPago = $this->formasPago[array_rand($this->formasPago)];
                $concepto = $tipo == 'ingreso' 
                    ? $this->conceptosIngreso[array_rand($this->conceptosIngreso)]
                    : $this->conceptosEgreso[array_rand($this->conceptosEgreso)];
                
                // Fecha y hora dentro del rango de apertura-cierre
                $fechaApertura = new \DateTime($apertura->fecha_apertura);
                $fechaCierre = new \DateTime($apertura->fecha_cierre);
                $interval = $fechaCierre->getTimestamp() - $fechaApertura->getTimestamp();
                $randomTime = rand(0, $interval);
                $fechaMovimiento = (clone $fechaApertura)->modify("+{$randomTime} seconds");
                
                $movimiento = [
                    'caja_apertura_id' => $apertura->id,
                    'user_id' => $usuarios->random()->id,
                    'sucursal_id' => $apertura->sucursal_id,
                    'tipo' => $tipo,
                    'categoria' => $categoria,
                    'forma_pago' => $formaPago,
                    'monto' => $monto,
                    'concepto' => $concepto . ' - ' . $fechaMovimiento->format('H:i'),
                    'referencia' => 'REF-' . rand(1000, 9999),
                    'requiere_autorizacion' => $monto > 1500 ? true : false,
                    'autorizado_por' => $monto > 1500 ? $usuarios->first()->id : null,
                    'autorizado_en' => $monto > 1500 ? $fechaMovimiento->modify('+5 minutes')->format('Y-m-d H:i:s') : null,
                    'created_at' => $fechaMovimiento->format('Y-m-d H:i:s'),
                    'updated_at' => $fechaMovimiento->format('Y-m-d H:i:s'),
                ];
                
                CajaMovimiento::create($movimiento);
                
                if ($tipo == 'ingreso') {
                    $totalIngresos += $monto;
                } else {
                    $totalEgresos += $monto;
                }
            }
            
            // Actualizar totales de la apertura
            $apertura->update([
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'monto_final' => $apertura->monto_inicial + $totalIngresos - $totalEgresos,
            ]);
        }
    }
}