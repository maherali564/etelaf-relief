<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('videos')->nullable()->after('video_url');
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->json('videos')->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('videos');
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('videos');
        });
    }
};
