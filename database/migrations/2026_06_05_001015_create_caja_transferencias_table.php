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
        Schema::create('caja_transferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_origen_id');
            $table->unsignedBigInteger('caja_destino_id');
            $table->unsignedBigInteger('caja_apertura_origen_id');
            $table->unsignedBigInteger('caja_apertura_destino_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('autorizado_por')->nullable();
            $table->decimal('monto', 12, 2);
            $table->text('motivo');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'completada'])->default('pendiente');
            $table->timestamp('autorizado_en')->nullable();
            $table->timestamps();
            
            $table->foreign('caja_origen_id')->references('id')->on('cajas');
            $table->foreign('caja_destino_id')->references('id')->on('cajas');
            $table->foreign('caja_apertura_origen_id')->references('id')->on('caja_aperturas');
            $table->foreign('caja_apertura_destino_id')->references('id')->on('caja_aperturas');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('autorizado_por')->references('id')->on('users');
            $table->index(['estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_transferencias');
    }
};
