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
        Schema::table('pedimentos_operaciones', function (Blueprint $table) {
            $table->string('moneda', 10)->nullable()->default('MXN')->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedimentos_operaciones', function (Blueprint $table) {
            $table->dropColumn('moneda');
        });
    }
};
