<?php
// app/Models/FormaPago.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormaPago extends Model
{
    protected $table = 'forma_pagos';

    protected $fillable = [
        'clave',
        'nombre',
        'icono',
        'orden',
        'requiere_referencia',
        'requiere_autorizacion',
        'activo_global'
    ];

    protected $casts = [
        'requiere_referencia' => 'boolean',
        'requiere_autorizacion' => 'boolean',
        'activo_global' => 'boolean',
        'orden' => 'integer'
    ];

    /**
     * Relación con empresas a través de la tabla pivote
     */
    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresa_forma_pagos', 'forma_pago_id', 'empresa_id')
            ->withPivot('activo', 'orden_empresa')
            ->withTimestamps();
    }

    /**
     * Obtener formas de pago activas para una empresa específica
     */
    public static function getActivasPorEmpresa($empresaId)
    {
        // Formas de pago activas globalmente Y que están activas en la empresa
        return self::where('activo_global', true)
            ->whereExists(function ($sub) use ($empresaId) {
                $sub->select(DB::raw(1))
                    ->from('empresa_forma_pagos')
                    ->whereColumn('empresa_forma_pagos.forma_pago_id', 'forma_pagos.id')
                    ->where('empresa_forma_pagos.empresa_id', $empresaId)
                    ->where('empresa_forma_pagos.activo', true);
            })
            ->orderBy('orden')
            ->get();
    }

    /**
     * Obtener TODAS las formas de pago con su estado para una empresa
     */
    public static function getConEstadoPorEmpresa($empresaId)
    {
        return self::where('activo_global', true)
            ->leftJoin('empresa_forma_pagos', function ($join) use ($empresaId) {
                $join->on('empresa_forma_pagos.forma_pago_id', '=', 'forma_pagos.id')
                    ->where('empresa_forma_pagos.empresa_id', '=', $empresaId);
            })
            ->select(
                'forma_pagos.*',
                DB::raw('COALESCE(empresa_forma_pagos.activo, true) as activo_empresa'),
                DB::raw('COALESCE(empresa_forma_pagos.orden_empresa, forma_pagos.orden) as orden_empresa')
            )
            ->orderBy('orden_empresa')
            ->get();
    }

    /**
     * Activar/desactivar una forma de pago para una empresa
     */
    public static function toggleParaEmpresa($formaPagoId, $empresaId, $activo = true)
    {
        $exists = DB::table('empresa_forma_pagos')
            ->where('forma_pago_id', $formaPagoId)
            ->where('empresa_id', $empresaId)
            ->exists();

        if ($exists) {
            DB::table('empresa_forma_pagos')
                ->where('forma_pago_id', $formaPagoId)
                ->where('empresa_id', $empresaId)
                ->update(['activo' => $activo]);
        } else {
            DB::table('empresa_forma_pagos')->insert([
                'forma_pago_id' => $formaPagoId,
                'empresa_id' => $empresaId,
                'activo' => $activo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Obtener configuración de formas de pago para una empresa
     */
    public static function getConfiguracionPorEmpresa($empresaId)
    {
        $configuraciones = DB::table('empresa_forma_pagos')
            ->where('empresa_id', $empresaId)
            ->pluck('activo', 'forma_pago_id')
            ->toArray();

        return $configuraciones;
    }
    /**
     * Mostrar detalle de una forma de pago
     */
    public function show(FormaPago $formaPago)
    {
        try {
            // Obtener empresas donde está configurada esta forma de pago
            $empresasActivas = EmpresaFormaPago::where('forma_pago_id', $formaPago->id)
                ->with('empresa')
                ->orderBy('orden_empresa')
                ->get();

            return view('formas_pago.show', compact('formaPago', 'empresasActivas'));
        } catch (\Exception $e) {
            Log::error('Error al mostrar forma de pago: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los detalles de la forma de pago.');
        }
    }
}