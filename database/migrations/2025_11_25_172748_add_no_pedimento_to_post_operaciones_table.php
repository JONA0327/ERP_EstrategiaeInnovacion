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
        Schema::table('post_operaciones', function (Blueprint $table) {
            $table->string('no_pedimento')->nullable()->after('operacion_logistica_id')
                  ->comment('Número de pedimento para asociar post-operaciones específicas por operación');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_operaciones', function (Blueprint $table) {
            $table->dropColumn('no_pedimento');
        });
    }
};
