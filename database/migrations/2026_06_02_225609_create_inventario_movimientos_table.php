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
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_origen_id')->nullable();
            $table->unsignedBigInteger('sucursal_destino_id')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('insumo_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->enum('tipo', ['entrada', 'salida', 'transferencia', 'ajuste']);
            $table->enum('motivo', ['compra', 'venta', 'devolucion', 'merma', 'transferencia', 'ajuste_inventario']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('costo_unitario', 10, 2)->default(0);
            $table->decimal('costo_total', 10, 2)->default(0);
            $table->text('observacion')->nullable();
            $table->string('referencia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
