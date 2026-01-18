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
        // Agregar descuentos a detalle_ventas (descuentos por producto)
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('precio_unitario');
            $table->decimal('descuento_monto', 10, 2)->default(0)->after('descuento_porcentaje');
        });

        // Agregar descuentos a ventas (descuento sobre el total de la venta)
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('total');
            $table->decimal('descuento_monto', 10, 2)->default(0)->after('descuento_porcentaje');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropColumn(['descuento_porcentaje', 'descuento_monto']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['descuento_porcentaje', 'descuento_monto']);
        });
    }
};
