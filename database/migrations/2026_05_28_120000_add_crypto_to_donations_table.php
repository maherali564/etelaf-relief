<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->foreignId('cryptocurrency_id')->nullable()->after('story_id')->constrained()->nullOnDelete();
            $table->foreignId('crypto_network_id')->nullable()->after('cryptocurrency_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crypto_network_id');
            $table->dropConstrainedForeignId('cryptocurrency_id');
        });
    }
};
