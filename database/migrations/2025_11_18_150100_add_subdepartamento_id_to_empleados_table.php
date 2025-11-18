<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->foreignId('subdepartamento_id')->nullable()->after('area')->constrained('subdepartamentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['subdepartamento_id']);
            $table->dropColumn('subdepartamento_id');
        });
    }
};
