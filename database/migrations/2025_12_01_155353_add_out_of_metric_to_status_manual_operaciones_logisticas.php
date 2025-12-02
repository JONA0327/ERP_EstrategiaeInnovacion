<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el enum de status_manual para incluir 'Out of Metric'
        DB::statement("ALTER TABLE operaciones_logisticas MODIFY COLUMN status_manual ENUM('In Process', 'Done', 'Out of Metric') NOT NULL DEFAULT 'In Process'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales
        DB::statement("ALTER TABLE operaciones_logisticas MODIFY COLUMN status_manual ENUM('In Process', 'Done') NOT NULL DEFAULT 'In Process'");
    }
};
