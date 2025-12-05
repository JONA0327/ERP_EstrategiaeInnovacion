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
        // 1. Eliminar columnas innecesarias de la tabla pedimentos
        Schema::table('pedimentos', function (Blueprint $table) {
            // Eliminar columnas que ya existen en pedimentos_operaciones
            if (Schema::hasColumn('pedimentos', 'subcategoria')) {
                $table->dropColumn('subcategoria');
            }
            if (Schema::hasColumn('pedimentos', 'fecha_pago')) {
                $table->dropColumn('fecha_pago');
            }
            if (Schema::hasColumn('pedimentos', 'monto')) {
                $table->dropColumn('monto');
            }
            if (Schema::hasColumn('pedimentos', 'moneda')) {
                $table->dropColumn('moneda');
            }
            if (Schema::hasColumn('pedimentos', 'observaciones_pago')) {
                $table->dropColumn('observaciones_pago');
            }
        });

        // 2. Vaciar y resetear ID de operaciones_logisticas
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        DB::table('operaciones_logisticas')->truncate();
        DB::statement('ALTER TABLE operaciones_logisticas AUTO_INCREMENT = 1');
        
        // 3. Vaciar y resetear ID de pedimentos
        DB::table('pedimentos')->truncate();
        DB::statement('ALTER TABLE pedimentos AUTO_INCREMENT = 1');
        
        // 4. Vaciar y resetear ID de pedimentos_operaciones
        DB::table('pedimentos_operaciones')->truncate();
        DB::statement('ALTER TABLE pedimentos_operaciones AUTO_INCREMENT = 1');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columnas eliminadas en pedimentos
        Schema::table('pedimentos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedimentos', 'subcategoria')) {
                $table->string('subcategoria')->nullable()->after('categoria');
            }
            if (!Schema::hasColumn('pedimentos', 'fecha_pago')) {
                $table->date('fecha_pago')->nullable()->after('estado_pago');
            }
            if (!Schema::hasColumn('pedimentos', 'monto')) {
                $table->decimal('monto', 10, 2)->nullable()->after('fecha_tentativa_pago');
            }
            if (!Schema::hasColumn('pedimentos', 'moneda')) {
                $table->string('moneda', 10)->default('USD')->after('monto');
            }
            if (!Schema::hasColumn('pedimentos', 'observaciones_pago')) {
                $table->text('observaciones_pago')->nullable()->after('moneda');
            }
        });
    }
};
