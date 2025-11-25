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
            $table->text('comentarios')->nullable()->after('fecha_ultimo_calculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operaciones_logisticas', function (Blueprint $table) {
            $table->dropColumn('comentarios');
        });
    }
};
