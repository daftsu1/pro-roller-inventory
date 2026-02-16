<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_conteos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conteo_fisico_id')->constrained('conteos_fisicos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->decimal('cantidad_sistema', 10, 2)->default(0);
            $table->integer('cantidad_fisica')->default(0); // Solo números enteros
            $table->decimal('diferencia', 10, 2)->default(0);
            $table->boolean('escaneado')->default(false);
            $table->timestamp('ultima_actualizacion')->nullable();
            $table->timestamps();
            
            $table->unique(['conteo_fisico_id', 'producto_id']);
            $table->index('conteo_fisico_id');
            $table->index('producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_conteos');
    }
};
