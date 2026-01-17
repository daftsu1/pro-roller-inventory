<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Índices para productos
        try {
            Schema::table('productos', function (Blueprint $table) {
                $table->index('activo', 'productos_activo_index');
                $table->index(['activo', 'stock_actual'], 'productos_activo_stock_index');
            });
        } catch (\Exception $e) {
            // El índice ya existe, continuar
        }

        // Índices para ventas
        try {
            Schema::table('ventas', function (Blueprint $table) {
                $table->index(['estado', 'fecha'], 'ventas_estado_fecha_index');
                $table->index('usuario_id', 'ventas_usuario_id_index');
            });
        } catch (\Exception $e) {
            // El índice ya existe, continuar
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('productos_activo_index');
            $table->dropIndex('productos_activo_stock_index');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_estado_fecha_index');
            $table->dropIndex('ventas_usuario_id_index');
        });
    }
};
