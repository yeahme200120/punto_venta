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
        Schema::create('empresa_licencia_historials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('licencia_id');
            $table->date('fecha_inicio_original')->nullable(); // Fecha inicio original de la empresa
            $table->date('fecha_inicio_periodo'); // Inicio del período de esta licencia
            $table->date('fecha_fin_periodo'); // Fin del período de esta licencia
            $table->decimal('monto_pagado', 10, 2);
            $table->string('referencia_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_licencia_historials');
    }
};
