<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('producto_relacionados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('producto_relacionado_id');
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices y claves foráneas
            $table->foreign('producto_id')
                  ->references('id')
                  ->on('productos')
                  ->onDelete('cascade');
                  
            $table->foreign('producto_relacionado_id')
                  ->references('id')
                  ->on('productos')
                  ->onDelete('cascade');
            
            // Índice único para evitar duplicados
            $table->unique(['producto_id', 'producto_relacionado_id'], 'unique_producto_relacion');
            
            // Índices para mejorar rendimiento
            $table->index(['producto_id', 'activo']);
            $table->index(['producto_relacionado_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_relacionados');
    }
};