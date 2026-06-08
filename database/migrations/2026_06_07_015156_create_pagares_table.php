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
        Schema::create('pagares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')->constrained()->onDelete('cascade');
            $table->string('folio', 20)->unique();
            $table->integer('numero_pago');
            $table->decimal('monto', 12, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->datetime('fecha_pago')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagares');
    }
};
