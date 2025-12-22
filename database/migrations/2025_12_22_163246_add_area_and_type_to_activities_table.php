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
            $table->string('area')->after('user_id')->nullable(); // Ej: Sistemas, RH, Logística
            $table->string('tipo_actividad')->after('nombre_actividad')->nullable(); // Ej: Soporte, Desarrollo, Bomberazo
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['area', 'tipo_actividad']);
        });
    }
};
