<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('gaza_stats');
    }

    public function down(): void
    {
        // Restoring is handled by the original migration
    }
};
