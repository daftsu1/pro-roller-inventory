<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida']);
            $table->decimal('cantidad', 10, 2);
            $table->string('motivo');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->date('fecha');
            $table->string('referencia')->nullable();
            
            // Relación opcional con ventas (híbrido)
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->foreignId('detalle_venta_id')->nullable()->constrained('detalle_ventas')->onDelete('set null');
            
            $table->timestamps();
            
            $table->index('producto_id');
            $table->index('venta_id');
            $table->index('fecha');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
