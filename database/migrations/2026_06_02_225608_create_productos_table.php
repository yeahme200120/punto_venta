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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->string('sku')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('costo_compra', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(5);
            $table->decimal('stock_maximo', 10, 2)->default(100);
            $table->boolean('control_inventario')->default(true);
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
        Schema::dropIfExists('productos');
    }
};
