<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('problema_archivo')) {
            Schema::drop('problema_archivo');
        }
    }

    public function down(): void
    {
        // Intentionally left blank; table is deprecated.
    }
};
