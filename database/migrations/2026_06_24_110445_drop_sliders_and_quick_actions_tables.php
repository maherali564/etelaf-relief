<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('quick_actions');
        Schema::dropIfExists('sliders');
    }

    public function down(): void
    {
    }
};
