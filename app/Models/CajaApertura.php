<?php
// app/Models/CajaApertura.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaApertura extends Model
{
    protected $table = 'caja_aperturas';

    protected $fillable = [
        'caja_id',
        'user_id',
        'empresa_id',
        'sucursal_id',
        'fecha',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_final',
        'total_ingresos',
        'total_egresos',
        'estado',
        'observaciones_apertura',
        'observaciones_cierre'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'total_ingresos' => 'decimal:2',
        'total_egresos' => 'decimal:2'
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function saldoActual()
    {
        $ingresos = $this->movimientos()->where('tipo', 'ingreso')->sum('monto');
        $egresos = $this->movimientos()->where('tipo', 'egreso')->sum('monto');
        return $this->monto_inicial + $ingresos - $egresos;
    }

    public function cerrar($montoFinal, $observaciones = null)
    {
        $this->update([
            'fecha_cierre' => now(),
            'monto_final' => $montoFinal,
            'estado' => 'cerrada',
            'observaciones_cierre' => $observaciones
        ]);

        // Actualizar saldo de la caja
        $this->caja->update(['saldo_actual' => $montoFinal]);
    }
    // En app/Models/CajaApertura.php agregar:
    public function arqueos()
    {
        return $this->hasMany(CajaArqueo::class);
    }

    public function ultimoArqueo()
    {
        return $this->hasOne(CajaArqueo::class)->latest();
    }
}