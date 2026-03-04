<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('tiene_instalacion')->default(false)->after('descuento_monto');
            $table->decimal('monto_instalacion', 10, 2)->default(0)->after('tiene_instalacion');
            $table->index('tiene_instalacion');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['tiene_instalacion']);
            $table->dropColumn(['tiene_instalacion', 'monto_instalacion']);
        });
    }
};
