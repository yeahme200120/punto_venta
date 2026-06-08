<?php
// database/seeders/CajaTransferenciaSeeder.php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CajaApertura;
use App\Models\CajaTransferencia;
use App\Models\User;
use Illuminate\Database\Seeder;

class CajaTransferenciaSeeder extends Seeder
{
    public function run(): void
    {
        $cajas = Caja::where('activo', true)->get();
        $usuarios = User::all();
        $aperturas = CajaApertura::where('estado', 'cerrada')->get();
        
        $transferencias = [];
        
        // Generar transferencias entre cajas de la misma sucursal
        foreach ($aperturas as $apertura) {
            // Solo algunas aperturas tienen transferencias (30%)
            if (rand(1, 100) <= 30) {
                // Buscar otra caja de la misma sucursal
                $otraCaja = Caja::where('sucursal_id', $apertura->sucursal_id)
                    ->where('id', '!=', $apertura->caja_id)
                    ->first();
                
                if ($otraCaja) {
                    $aperturaDestino = CajaApertura::where('caja_id', $otraCaja->id)
                        ->whereDate('fecha', $apertura->fecha)
                        ->first();
                    
                    if ($aperturaDestino) {
                        $monto = rand(500, 3000);
                        
                        $transferencias[] = [
                            'caja_origen_id' => $apertura->caja_id,
                            'caja_destino_id' => $otraCaja->id,
                            'caja_apertura_origen_id' => $apertura->id,
                            'caja_apertura_destino_id' => $aperturaDestino->id,
                            'user_id' => $usuarios->random()->id,
                            'autorizado_por' => $usuarios->first()->id,
                            'monto' => $monto,
                            'motivo' => 'Traspaso de fondos para cubrir necesidades',
                            'estado' => 'aprobada',
                            'autorizado_en' => $apertura->fecha_apertura,
                            'created_at' => $apertura->fecha_apertura,
                            'updated_at' => $apertura->fecha_apertura,
                        ];
                    }
                }
            }
        }
        
        foreach ($transferencias as $transferencia) {
            CajaTransferencia::create($transferencia);
        }
    }
}