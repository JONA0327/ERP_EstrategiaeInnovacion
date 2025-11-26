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
            // Agregar campo para status manual (controlado por el usuario)
            $table->enum('status_manual', ['In Process', 'Done'])->default('In Process')->after('status_calculado');
            $table->timestamp('fecha_status_manual')->nullable()->after('status_manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            $table->dropColumn(['status_manual', 'fecha_status_manual']);
        });
    }
};
