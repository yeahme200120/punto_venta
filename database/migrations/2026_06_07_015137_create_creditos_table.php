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
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained();
            $table->foreignId('sucursal_id')->constrained();
            $table->foreignId('venta_id')->constrained();
            $table->foreignId('cliente_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('saldo_pendiente', 12, 2);
            $table->enum('plazo', ['7_dias', '15_dias', '1_mes', '2_meses', '3_meses', '6_meses', '1_ano']);
            $table->integer('num_pagos');
            $table->enum('estado', ['activo', 'pagado', 'vencido', 'cancelado'])->default('activo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};
