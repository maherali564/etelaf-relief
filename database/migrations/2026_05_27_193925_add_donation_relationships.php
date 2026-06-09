<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add goal_amount/raised_amount to projects
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'goal_amount')) {
                $table->decimal('goal_amount', 14, 2)->default(0)->after('content');
            }
            if (! Schema::hasColumn('projects', 'raised_amount')) {
                $table->decimal('raised_amount', 14, 2)->default(0)->after('goal_amount');
            }
        });

        // Add campaign_id to posts
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete()->after('image');
            }
        });

        // Add campaign_id, project_id, post_id, donated_at to donations
        Schema::table('donations', function (Blueprint $table) {
            if (! Schema::hasColumn('donations', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete()->after('notes');
            }
            if (! Schema::hasColumn('donations', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete()->after('campaign_id');
            }
            if (! Schema::hasColumn('donations', 'post_id')) {
                $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete()->after('project_id');
            }
            if (! Schema::hasColumn('donations', 'donated_at')) {
                $table->timestamp('donated_at')->nullable()->after('post_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['project_id']);
            $table->dropForeign(['post_id']);
            $table->dropColumn(['campaign_id', 'project_id', 'post_id', 'donated_at']);
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['goal_amount', 'raised_amount']);
        });
    }
};
