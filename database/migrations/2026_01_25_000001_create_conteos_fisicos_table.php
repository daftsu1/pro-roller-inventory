<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conteos_fisicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha_conteo');
            $table->enum('estado', ['pendiente', 'en_proceso', 'finalizado', 'cancelado'])->default('pendiente');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index('estado');
            $table->index('fecha_conteo');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conteos_fisicos');
    }
};
