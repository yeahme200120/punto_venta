<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unidad_medidas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 100); // Tipo de unidad
            $table->string('clave', 10)->unique(); // Clave de la unidad (H87, EA, etc.)
            $table->string('nombre', 100); // Nombre de la unidad
            $table->text('descripcion')->nullable(); // Descripción
            $table->string('simbolo', 20)->nullable(); // Símbolo (kg, m, l, etc.)
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidad_medidas');
    }
};
