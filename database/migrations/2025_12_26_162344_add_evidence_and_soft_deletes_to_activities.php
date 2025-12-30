<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            // Campo para la ruta del archivo (PDF, Imagen, etc.)
            $table->string('evidencia_path')->nullable()->after('comentarios');
            // Campo para "borrado suave" (no borra el registro, solo lo marca)
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('evidencia_path');
            $table->dropSoftDeletes();
        });
    }
};