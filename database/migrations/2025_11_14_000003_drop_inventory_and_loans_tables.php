<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop dependent tables first
        if (Schema::hasTable('discos_en_uso')) {
            Schema::drop('discos_en_uso');
        }
        if (Schema::hasTable('prestamos_inventario')) {
            Schema::drop('prestamos_inventario');
        }
        if (Schema::hasTable('inventarios')) {
            Schema::drop('inventarios');
        }
    }

    public function down(): void
    {
        // Intentionally not recreating tables
    }
};
