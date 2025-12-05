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
        Schema::table('pedimentos', function (Blueprint $table) {
            // Eliminar estado_pago y fecha_tentativa_pago ya que están en pedimentos_operaciones
            if (Schema::hasColumn('pedimentos', 'estado_pago')) {
                $table->dropColumn('estado_pago');
            }
            if (Schema::hasColumn('pedimentos', 'fecha_tentativa_pago')) {
                $table->dropColumn('fecha_tentativa_pago');
            }
            // Por si acaso quedó categoria también
            if (Schema::hasColumn('pedimentos', 'categoria')) {
                $table->dropColumn('categoria');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedimentos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedimentos', 'estado_pago')) {
                $table->enum('estado_pago', ['pendiente', 'pagado'])->default('pendiente')->after('descripcion');
            }
            if (!Schema::hasColumn('pedimentos', 'fecha_tentativa_pago')) {
                $table->date('fecha_tentativa_pago')->nullable()->after('estado_pago');
            }
            if (!Schema::hasColumn('pedimentos', 'categoria')) {
                $table->string('categoria')->nullable()->after('id');
            }
        });
    }
};
