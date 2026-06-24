<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('faqs');
    }

    public function down(): void
    {
        // No restore — table was removed intentionally
    }
};
