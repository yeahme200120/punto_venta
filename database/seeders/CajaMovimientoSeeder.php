<?php
// database/seeders/CajaMovimientoSeeder.php
namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Models\User;
use Illuminate\Database\Seeder;

class CajaMovimientoSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = \App\Models\Empresa::all();
        
        foreach ($empresas as $empresa) {
            $sucursales = \App\Models\Sucursal::where('empresa_id', $empresa->id)->get();
            
            foreach ($sucursales as $sucursal) {
                // Crear caja si no existe
                $caja = Caja::firstOrCreate(
                    ['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal->id, 'nombre' => 'Caja Principal'],
                    [
                        'codigo' => Caja::generarCodigo(),
                        'saldo_inicial' => 5000,
                        'saldo_actual' => 5000,
                        'activo' => true,
                        'permite_multiple' => true
                    ]
                );
                
                $usuarios = User::where('empresa_id', $empresa->id)->get();
                if ($usuarios->isEmpty()) continue;
                
                $usuario = $usuarios->first();
                
                // Crear aperturas de los últimos 3 meses
                $fechas = [
                    now()->subMonths(2)->startOfMonth(),
                    now()->subMonth()->startOfMonth(),
                    now()->startOfMonth(),
                ];
                
                $tipos = ['ingreso', 'egreso'];
                $categorias = [
                    'ingreso' => ['venta', 'abono_credito', 'cobro_servicio', 'transferencia'],
                    'egreso' => ['compra', 'gasto', 'retiro', 'transferencia']
                ];
                $formasPago = ['efectivo', 'tarjeta_debito', 'tarjeta_credito', 'vale', 'transferencia'];
                
                foreach ($fechas as $fechaInicio) {
                    $fechaFin = $fechaInicio->copy()->endOfMonth();
                    
                    // Apertura mensual
                    $apertura = CajaApertura::create([
                        'caja_id' => $caja->id,
                        'user_id' => $usuario->id,
                        'sucursal_id' => $sucursal->id,
                        'fecha' => $fechaInicio,
                        'fecha_apertura' => $fechaInicio->copy()->setTime(8, 0),
                        'fecha_cierre' => $fechaFin->copy()->setTime(20, 0),
                        'monto_inicial' => 5000,
                        'monto_final' => rand(8000, 15000),
                        'estado' => 'cerrada',
                    ]);
                    
                    $totalIngresos = 0;
                    $totalEgresos = 0;
                    
                    // Generar movimientos diarios
                    $currentDate = $fechaInicio->copy();
                    while ($currentDate <= $fechaFin) {
                        // Saltar fines de semana para que sea más realista
                        if (!in_array($currentDate->dayOfWeek, [0, 6])) {
                            // Ingresos del día (2-5 movimientos)
                            $numIngresos = rand(2, 5);
                            for ($i = 0; $i < $numIngresos; $i++) {
                                $monto = rand(100, 5000);
                                $categoria = $categorias['ingreso'][array_rand($categorias['ingreso'])];
                                $formaPago = $formasPago[array_rand($formasPago)];
                                
                                CajaMovimiento::create([
                                    'caja_apertura_id' => $apertura->id,
                                    'user_id' => $usuario->id,
                                    'sucursal_id' => $sucursal->id,
                                    'tipo' => 'ingreso',
                                    'categoria' => $categoria,
                                    'forma_pago' => $formaPago,
                                    'monto' => $monto,
                                    'concepto' => $this->getConceptoIngreso($categoria),
                                    'referencia' => 'REF-' . strtoupper(uniqid()),
                                    'created_at' => $currentDate->copy()->setTime(rand(10, 19), rand(0, 59)),
                                    'updated_at' => $currentDate->copy()->setTime(rand(10, 19), rand(0, 59))
                                ]);
                                $totalIngresos += $monto;
                            }
                            
                            // Egresos del día (1-3 movimientos)
                            $numEgresos = rand(1, 3);
                            for ($i = 0; $i < $numEgresos; $i++) {
                                $monto = rand(50, 2000);
                                $categoria = $categorias['egreso'][array_rand($categorias['egreso'])];
                                $formaPago = $formasPago[array_rand($formasPago)];
                                
                                CajaMovimiento::create([
                                    'caja_apertura_id' => $apertura->id,
                                    'user_id' => $usuario->id,
                                    'sucursal_id' => $sucursal->id,
                                    'tipo' => 'egreso',
                                    'categoria' => $categoria,
                                    'forma_pago' => $formaPago,
                                    'monto' => $monto,
                                    'concepto' => $this->getConceptoEgreso($categoria),
                                    'referencia' => 'REF-' . strtoupper(uniqid()),
                                    'created_at' => $currentDate->copy()->setTime(rand(10, 19), rand(0, 59)),
                                    'updated_at' => $currentDate->copy()->setTime(rand(10, 19), rand(0, 59))
                                ]);
                                $totalEgresos += $monto;
                            }
                        }
                        $currentDate->addDay();
                    }
                    
                    // Actualizar totales de la apertura
                    $apertura->update([
                        'total_ingresos' => $totalIngresos,
                        'total_egresos' => $totalEgresos,
                        'monto_final' => $apertura->monto_inicial + $totalIngresos - $totalEgresos
                    ]);
                    
                    $this->command->info("Movimientos creados para {$sucursal->nombre} - {$fechaInicio->format('M Y')}");
                }
            }
        }
    }
    
    private function getConceptoIngreso($categoria)
    {
        $conceptos = [
            'venta' => ['Venta de producto', 'Venta al contado', 'Venta con tarjeta', 'Venta mostrador'],
            'abono_credito' => ['Abono a crédito cliente', 'Pago parcial factura', 'Abono mensual'],
            'cobro_servicio' => ['Cobro de mantenimiento', 'Servicio técnico', 'Instalación'],
            'transferencia' => ['Transferencia recibida', 'Depósito bancario', 'Transferencia otra sucursal']
        ];
        
        $lista = $conceptos[$categoria] ?? ['Ingreso registrado'];
        return $lista[array_rand($lista)];
    }
    
    private function getConceptoEgreso($categoria)
    {
        $conceptos = [
            'compra' => ['Compra a proveedor', 'Adquisición insumos', 'Compra mercancía'],
            'gasto' => ['Pago de servicios', 'Gastos operativos', 'Papelería', 'Limpieza'],
            'retiro' => ['Retiro personal', 'Gastos menores', 'Caja chica'],
            'transferencia' => ['Transferencia enviada', 'Pago proveedor', 'Transferencia otra caja']
        ];
        
        $lista = $conceptos[$categoria] ?? ['Egreso registrado'];
        return $lista[array_rand($lista)];
    }
}