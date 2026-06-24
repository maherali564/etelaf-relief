<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('video')->nullable()->after('videos');
            $table->string('video_thumbnail')->nullable()->after('video');
            $table->string('video_status')->default('pending')->after('video_thumbnail');
            $table->index('video_status');
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->string('video')->nullable()->after('videos');
            $table->string('video_thumbnail')->nullable()->after('video');
            $table->string('video_status')->default('pending')->after('video_thumbnail');
            $table->index('video_status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['video', 'video_thumbnail', 'video_status']);
        });
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['video', 'video_thumbnail', 'video_status']);
        });
    }
};