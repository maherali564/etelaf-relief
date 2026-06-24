<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('video_type')->nullable()->after('video_url');
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->string('video_type')->nullable()->after('video_url');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('images');
            $table->string('video_type')->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('video_type');
        });

        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('video_type');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'video_type']);
        });
    }
};
