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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('show_announcements')->default(true)->after('about_features');
            $table->boolean('show_success_stories')->default(true)->after('show_announcements');
            $table->boolean('show_donor_wall')->default(true)->after('show_success_stories');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['show_announcements', 'show_success_stories', 'show_donor_wall']);
        });
    }
};
