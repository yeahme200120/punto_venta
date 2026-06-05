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
        Schema::create('caja_arqueos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_apertura_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->dateTime('fecha_arqueo');
            
            // Desglose por forma de pago
            $table->decimal('efectivo_contado', 12, 2)->default(0);
            $table->decimal('tarjeta_debito_contado', 12, 2)->default(0);
            $table->decimal('tarjeta_credito_contado', 12, 2)->default(0);
            $table->decimal('vale_contado', 12, 2)->default(0);
            $table->decimal('transferencia_contado', 12, 2)->default(0);
            $table->decimal('cheque_contado', 12, 2)->default(0);
            
            // Totales
            $table->decimal('total_contado', 12, 2)->default(0);
            $table->decimal('total_sistema', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);
            
            // Observaciones
            $table->text('observaciones')->nullable();
            $table->string('comprobante_imagen')->nullable();
            
            $table->enum('estado', ['borrador', 'finalizado'])->default('borrador');
            $table->timestamps();
            
            $table->foreign('caja_apertura_id')->references('id')->on('caja_aperturas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('sucursal_id')->references('id')->on('sucursals');
            $table->index(['caja_apertura_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_arqueos');
    }
};
