<?php

namespace App\Traits;

use App\Models\CajaApertura;

trait FiltrosPorRol
{
    /**
     * Aplicar filtro de apertura según rol del usuario
     */
    protected function aplicarFiltroApertura($query, $empresaId, $sucursalId, $userId = null)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super Admin')) {
            // Super Admin: ve todas las aperturas de la empresa/sucursal
            return $query->where('empresa_id', $empresaId)
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId));
        } elseif ($user->hasRole('Administrador')) {
            // Admin: ve todas las aperturas de su empresa/sucursal activa
            return $query->where('empresa_id', $empresaId)
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId));
        } else {
            // Cajero/Vendedor: solo ven sus propias aperturas
            return $query->where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('user_id', $userId);
        }
    }
    
    /**
     * Obtener apertura activa según rol
     */
    protected function getAperturaActiva($empresaId, $sucursalId, $userId = null)
    {
        $user = auth()->user();
        
        $query = CajaApertura::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'abierta');
        
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador')) {
            $query->where('user_id', $userId);
        }
        
        return $query->first();
    }
    
    /**
     * Verificar si el usuario tiene acceso a un registro según su rol
     */
    protected function tieneAcceso($registro, $empresaId, $userId = null)
    {
        $user = auth()->user();
        
        // Super Admin: acceso total
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        // Administrador: solo si es de su empresa
        if ($user->hasRole('Administrador')) {
            return isset($registro->empresa_id) && $registro->empresa_id == $empresaId;
        }
        
        // Usuarios normales: solo sus propios registros
        return isset($registro->user_id) && $registro->user_id == $userId;
    }
}