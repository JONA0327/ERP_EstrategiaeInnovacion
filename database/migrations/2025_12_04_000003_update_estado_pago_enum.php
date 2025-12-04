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
        // Actualizar enum para quitar 'vencido'
        DB::statement("ALTER TABLE pedimentos MODIFY estado_pago ENUM('pendiente', 'pagado') NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pedimentos MODIFY estado_pago ENUM('pendiente', 'pagado', 'vencido') NOT NULL DEFAULT 'pendiente'");
    }
};