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
        Schema::create('ticket_configuracions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('tipo'); // movimiento, transferencia, arqueo, cierre
            $table->string('nombre_empresa')->default('Mi Empresa');
            $table->string('logo_url')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('rfc')->nullable();
            $table->string('cabecera')->nullable();
            $table->string('footer')->default('¡Gracias por su compra!');
            $table->boolean('mostrar_logo')->default(false);
            $table->boolean('mostrar_direccion')->default(true);
            $table->boolean('mostrar_telefono')->default(true);
            $table->boolean('mostrar_email')->default(true);
            $table->boolean('mostrar_rfc')->default(true);
            $table->string('ancho_papel')->default('80mm');
            $table->string('fuente')->default('monospace');
            $table->integer('tamano_fuente')->default(12);
            $table->string('regimen_fiscal')->nullable();
            $table->string('uso_cfdi')->nullable();
            $table->boolean('mostrar_regimen')->default(false);
            $table->boolean('auto_imprimir')->default(true);
            $table->boolean('facturar')->default(true);
            $table->integer('copias')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['empresa_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_configuracions');
    }
};
