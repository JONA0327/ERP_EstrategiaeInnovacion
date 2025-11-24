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
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            // Solo agregar el campo ejecutivo, los demás ya existen
            $table->string('ejecutivo')->nullable()->after('ejecutivo_empleado_id')->comment('Nombre del ejecutivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            $table->dropColumn('ejecutivo');
        });
    }
};
