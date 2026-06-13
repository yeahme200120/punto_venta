<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'licencia_id',
        'nombre',
        'rfc',
        'direccion',
        'telefono',
        'correo',
        'logo',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    public function licencia()
    {
        return $this->belongsTo(Licencia::class, 'licencia_id');
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'empresa_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function historialLicencias()
    {
        return $this->hasMany(EmpresaLicenciaHistorial::class)->orderBy('created_at', 'desc');
    }

    /**
     * Obtener la licencia actualmente activa (último período del historial)
     */
    public function licenciaActiva()
    {
        // Primero intentar obtener del historial
        $historialActivo = $this->historialLicencias()
            ->where('fecha_fin_periodo', '>=', Carbon::now())
            ->first();

        if ($historialActivo) {
            return $historialActivo;
        }

        // Si no hay historial, usar los datos de la empresa (migración inicial)
        if ($this->licencia_id && $this->fecha_fin) {
            // Crear un objeto virtual para compatibilidad
            $virtual = new \stdClass();
            $virtual->licencia = $this->licencia;
            $virtual->fecha_inicio_periodo = $this->fecha_inicio;
            $virtual->fecha_fin_periodo = $this->fecha_fin;
            $virtual->licencia_id = $this->licencia_id;
            return $virtual;
        }

        return null;
    }

    /**
     * Verificar si la licencia está vigente (hasta las 23:59:59 del día de fin)
     */
    public function licenciaVigente(): bool
    {
        $licenciaActiva = $this->licenciaActiva();

        if (!$licenciaActiva) {
            return false;
        }

        $fechaFin = $licenciaActiva->fecha_fin_periodo ?? $this->fecha_fin;

        if (!$fechaFin) {
            return false;
        }

        if (!$fechaFin instanceof Carbon) {
            $fechaFin = Carbon::parse($fechaFin);
        }
        
        return $this->activo && $fechaFin->endOfDay()->isFuture();
    }

    /**
     * Obtener días restantes de licencia
     */
    public function diasRestantesLicencia(): int
    {
        $licenciaActiva = $this->licenciaActiva();

        if (!$licenciaActiva) {
            return 0;
        }

        $fechaFin = $licenciaActiva->fecha_fin_periodo ?? $this->fecha_fin;

        if (!$fechaFin) {
            return 0;
        }

        if (!$fechaFin instanceof \Carbon\Carbon) {
            $fechaFin = \Carbon\Carbon::parse($fechaFin);
        }

        $hoy = \Carbon\Carbon::now();
        $fechaFinFinDelDia = $fechaFin->copy()->endOfDay();

        if ($fechaFinFinDelDia < $hoy) {
            return 0;
        }

        // Calcular días completos restantes (redondeando hacia arriba)
        return max(1, ceil($hoy->diffInDays($fechaFinFinDelDia, false)));
    }

    // Accessor para obtener la URL completa del logo
    public function getLogoUrlAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return Storage::url($this->logo);
        }
        return null;
    }

    // Método para eliminar el logo del almacenamiento
    public function deleteLogo()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            Storage::disk('public')->delete($this->logo);
        }
        $this->logo = null;
        $this->save();
    }

    protected static function booted()
    {
        static::deleting(function ($empresa) {
            $empresa->deleteLogo();
        });
    }
}