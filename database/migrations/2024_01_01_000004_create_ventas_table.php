<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_factura')->unique();
            $table->date('fecha');
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_documento')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->enum('estado', ['pendiente', 'completada', 'cancelada'])->default('completada');
            $table->timestamps();
            
            $table->index('numero_factura');
            $table->index('fecha');
            $table->index('estado');
            $table->index(['estado', 'fecha']); // Para consultas filtradas por estado y fecha
            $table->index('usuario_id'); // Para consultas por usuario
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
