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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained();
            $table->foreignId('sucursal_id')->constrained();
            $table->foreignId('caja_apertura_id')->constrained('caja_aperturas');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('cliente_id')->nullable()->constrained();
            $table->string('folio', 20)->unique();
            $table->enum('tipo', ['contado', 'credito'])->default('contado');
            $table->enum('estado', ['completada', 'cancelada', 'pendiente'])->default('completada');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva', 12, 2);
            $table->decimal('total', 12, 2);
            $table->text('observaciones')->nullable();
            $table->datetime('fecha_venta');
            $table->timestamps();
            
            $table->index(['empresa_id', 'sucursal_id', 'fecha_venta']);
            $table->index('folio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
