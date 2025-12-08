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
        Schema::table('campos_personalizados_matriz', function (Blueprint $table) {
            $table->string('mostrar_despues_de', 50)->nullable()->after('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campos_personalizados_matriz', function (Blueprint $table) {
            $table->dropColumn('mostrar_despues_de');
        });
    }
};
