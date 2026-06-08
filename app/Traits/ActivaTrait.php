<?php
// app/Traits/ActivaTrait.php

namespace App\Traits;

use App\Models\Sucursal;

trait ActivaTrait
{
    private function empresaActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return session('empresa_activa_id', auth()->user()->empresa_id);
        }
        return auth()->user()->empresa_id;
    }

    private function sucursalActivaId()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            $sucursalId = session('sucursal_activa_id');
            
            // Si no hay sucursal en sesión, obtener la primera sucursal activa
            if (!$sucursalId) {
                $sucursal = Sucursal::where('empresa_id', $this->empresaActivaId())
                    ->where('activo', true)
                    ->first();
                if ($sucursal) {
                    $sucursalId = $sucursal->id;
                    session(['sucursal_activa_id' => $sucursalId]);
                }
            }
            
            // Si aún no hay, obtener de la caja abierta del usuario
            if (!$sucursalId) {
                $apertura = \App\Models\CajaApertura::where('user_id', auth()->id())
                    ->where('estado', 'abierta')
                    ->first();
                if ($apertura) {
                    $sucursalId = $apertura->sucursal_id;
                    session(['sucursal_activa_id' => $sucursalId]);
                }
            }
            
            return $sucursalId;
        }
        
        return auth()->user()->sucursal_id;
    }
    
    private function validarSucursalActiva()
    {
        $sucursalId = $this->sucursalActivaId();
        if (!$sucursalId) {
            throw new \Exception('No hay una sucursal activa. Selecciona una sucursal.');
        }
        return $sucursalId;
    }
}