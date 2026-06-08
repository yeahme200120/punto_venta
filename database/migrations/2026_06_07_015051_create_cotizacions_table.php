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
        Schema::create('cotizacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained();
            $table->foreignId('sucursal_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('cliente_id')->nullable()->constrained();
            $table->string('folio', 20)->unique();
            $table->enum('estado', ['activa', 'convertida', 'vencida', 'cancelada'])->default('activa');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva', 12, 2);
            $table->decimal('total', 12, 2);
            $table->text('observaciones')->nullable();
            $table->date('fecha_validez')->nullable();
            $table->datetime('fecha_cotizacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacions');
    }
};
