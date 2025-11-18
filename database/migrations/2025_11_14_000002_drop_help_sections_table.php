<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('help_sections')) {
            Schema::drop('help_sections');
        }
    }

    public function down(): void
    {
        // No-op. Help module removed.
    }
};
