<?php
// app/Models/CajaArqueo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaArqueo extends Model
{
    protected $table = 'caja_arqueos';
    
    protected $fillable = [
        'caja_apertura_id',
        'user_id',
        'sucursal_id',
        'fecha_arqueo',
        'efectivo_contado',
        'tarjeta_debito_contado',
        'tarjeta_credito_contado',
        'vale_contado',
        'transferencia_contado',
        'cheque_contado',
        'total_contado',
        'total_sistema',
        'diferencia',
        'observaciones',
        'comprobante_imagen',
        'estado'
    ];
    
    protected $casts = [
        'fecha_arqueo' => 'datetime',
        'efectivo_contado' => 'decimal:2',
        'tarjeta_debito_contado' => 'decimal:2',
        'tarjeta_credito_contado' => 'decimal:2',
        'vale_contado' => 'decimal:2',
        'transferencia_contado' => 'decimal:2',
        'cheque_contado' => 'decimal:2',
        'total_contado' => 'decimal:2',
        'total_sistema' => 'decimal:2',
        'diferencia' => 'decimal:2'
    ];
    
    public function cajaApertura()
    {
        return $this->belongsTo(CajaApertura::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    
    // Calcular total contado
    public function calcularTotalContado()
    {
        $this->total_contado = 
            $this->efectivo_contado + 
            $this->tarjeta_debito_contado + 
            $this->tarjeta_credito_contado + 
            $this->vale_contado + 
            $this->transferencia_contado + 
            $this->cheque_contado;
        return $this->total_contado;
    }
    
    // Calcular diferencia con sistema
    public function calcularDiferencia($totalSistema)
    {
        $this->total_sistema = $totalSistema;
        $this->diferencia = $this->total_contado - $totalSistema;
        return $this->diferencia;
    }
}