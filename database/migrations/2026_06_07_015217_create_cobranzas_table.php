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
        Schema::create('cobranzas', function (Blueprint $table) {
            $table->id();
             $table->foreignId('empresa_id')->constrained();
            $table->foreignId('sucursal_id')->constrained();
            $table->foreignId('credito_id')->nullable()->constrained();
            $table->foreignId('pagare_id')->nullable()->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('caja_movimiento_id')->nullable()->constrained();
            $table->decimal('monto', 12, 2);
            $table->enum('tipo', ['abono', 'pago_pagare', 'pago_total']);
            $table->text('observaciones')->nullable();
            $table->datetime('fecha_cobro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobranzas');
    }
};
