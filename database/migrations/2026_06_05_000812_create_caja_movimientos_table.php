<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_apertura_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->enum('categoria', [
                'venta', 'abono_credito', 'cobro_servicio', 'prestamo', 
                'compra', 'gasto', 'retiro', 'transferencia', 'ajuste'
            ]);
            $table->enum('forma_pago', ['efectivo', 'tarjeta_debito', 'tarjeta_credito', 'vale', 'transferencia', 'cheque']);
            $table->decimal('monto', 12, 2);
            $table->string('referencia')->nullable();
            $table->text('concepto');
            $table->string('comprobante')->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_type')->nullable();
            $table->boolean('requiere_autorizacion')->default(false);
            $table->unsignedBigInteger('autorizado_por')->nullable();
            $table->timestamp('autorizado_en')->nullable();
            $table->timestamps();
            
            $table->foreign('caja_apertura_id')->references('id')->on('caja_aperturas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('sucursal_id')->references('id')->on('sucursals');
            $table->foreign('autorizado_por')->references('id')->on('users');
            $table->index(['caja_apertura_id', 'tipo', 'forma_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};
