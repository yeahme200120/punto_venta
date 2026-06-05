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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida')->default('pieza');
            $table->unsignedBigInteger('unidad_medida_id')->nullable();
            $table->decimal('costo_unitario', 10, 2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(10);
            $table->decimal('stock_maximo', 10, 2)->default(500);
            $table->boolean('activo')->default(true);
            $table->timestamp('ultima_notificacion_stock')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
