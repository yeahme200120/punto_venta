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
        Schema::create('forma_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 50)->unique();
            $table->string('nombre', 100);
            $table->string('icono', 10)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('requiere_referencia')->default(false);
            $table->boolean('requiere_autorizacion')->default(false);
            $table->boolean('activo_global')->default(true);
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forma_pagos');
    }
};
