<?php
// app/Models/EmpresaFormaPago.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmpresaFormaPago extends Model
{
    protected $table = 'empresa_forma_pagos';
    
    protected $fillable = [
        'empresa_id', 'forma_pago_id', 'activo', 'orden_empresa'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'orden_empresa' => 'integer'
    ];
    
    /**
     * Relación con la empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    
    /**
     * Relación con la forma de pago global
     */
    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class, 'forma_pago_id');
    }
    
    /**
     * Obtener formas de pago activas de una empresa (como objetos FormaPago)
     */
    public static function getActivasPorEmpresa($empresaId)
    {
        return self::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->with('formaPago')
            ->orderBy('orden_empresa')
            ->get()
            ->map(function($item) {
                return $item->formaPago;
            });
    }
    
    /**
     * Activar/desactivar una forma de pago para una empresa
     */
    public static function toggle($empresaId, $formaPagoId, $activo = true)
    {
        $registro = self::where('empresa_id', $empresaId)
            ->where('forma_pago_id', $formaPagoId)
            ->first();
            
        if ($registro) {
            $registro->update(['activo' => $activo]);
        } else {
            self::create([
                'empresa_id' => $empresaId,
                'forma_pago_id' => $formaPagoId,
                'activo' => $activo,
                'orden_empresa' => 0
            ]);
        }
        
        return true;
    }
    
    /**
     * Sincronizar formas de pago para una empresa
     */
    public static function sincronizar($empresaId, array $formasActivasIds)
    {
        // Desactivar todas primero
        self::where('empresa_id', $empresaId)->update(['activo' => false]);
        
        // Activar las seleccionadas
        foreach ($formasActivasIds as $formaId) {
            self::toggle($empresaId, $formaId, true);
        }
        
        return true;
    }
    
    /**
     * Inicializar formas de pago para una nueva empresa
     */
    public static function inicializarParaNuevaEmpresa($empresaId)
    {
        $formasActivasGlobal = FormaPago::where('activo_global', true)->get();
        
        foreach ($formasActivasGlobal as $forma) {
            self::create([
                'empresa_id' => $empresaId,
                'forma_pago_id' => $forma->id,
                'activo' => true,
                'orden_empresa' => $forma->orden
            ]);
        }
        
        return true;
    }
}