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
            $table->enum('estado_pago', ['pendiente', 'pagado', 'vencido'])->default('pendiente')->after('descripcion');
            $table->date('fecha_pago')->nullable()->after('estado_pago');
            $table->decimal('monto', 10, 2)->nullable()->after('fecha_pago');
            $table->text('observaciones_pago')->nullable()->after('monto');
            $table->timestamp('fecha_vencimiento')->nullable()->after('observaciones_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedimentos', function (Blueprint $table) {
            $table->dropColumn(['estado_pago', 'fecha_pago', 'monto', 'observaciones_pago', 'fecha_vencimiento']);
        });
    }
};