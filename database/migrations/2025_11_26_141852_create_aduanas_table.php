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
        Schema::create('aduanas', function (Blueprint $table) {
            $table->id();
            $table->string('aduana', 2);
            $table->string('seccion', 1)->default('0');
            $table->text('denominacion');
            $table->timestamps();

            $table->index(['aduana','seccion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aduanas');
    }
};
