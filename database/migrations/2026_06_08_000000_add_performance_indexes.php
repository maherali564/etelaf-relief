<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        'donations' => [
            ['status', 'created_at'],
            'email',
            'donor_id',
            'payment_method_id',
            'campaign_id',
            'project_id',
            'story_id',
            'donated_at',
        ],
        'campaigns' => [
            'is_active',
        ],
        'projects' => [
            'is_active',
            'is_featured',
        ],
        'posts' => [
            ['type', 'is_active', 'published_at'],
            'is_active',
        ],
        'users' => [
            'role',
            'is_active',
        ],
        'donors' => [
            'is_active',
        ],
        'payment_methods' => [
            'gateway_id',
            'is_active',
        ],
        'payment_gateways' => [
            'driver',
            'is_active',
        ],
        'volunteers' => [
            'status',
            'email',
        ],
        'contact_submissions' => [
            'is_read',
        ],
        'donation_submissions' => [
            'status',
        ],
        'newsletters' => [
            'is_subscribed',
        ],
        'payment_confirmations' => [
            'donation_id',
            'status',
        ],
        'crypto_networks' => [
            'cryptocurrency_id',
            'is_active',
        ],
        'cryptocurrencies' => [
            'is_active',
        ],
        'stories' => [
            'is_active',
            'sort_order',
        ],
        'sliders' => [
            'is_active',
            'sort_order',
        ],
        'testimonials' => [
            'is_active',
        ],
        'faqs' => [
            'is_active',
            'sort_order',
        ],
        'statistics' => [
            'type',
            'is_active',
        ],
        'quick_actions' => [
            'is_active',
            'sort_order',
        ],
        'programs' => [
            'is_active',
            'sort_order',
        ],
        'gaza_stats' => [
            'is_active',
            'sort_order',
        ],
        'pages' => [
            'is_active',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $columns) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (is_array($column)) {
                        $name = implode('_', $column);
                        $tableBlueprint->index($column, "idx_{$table}_{$name}");
                    } else {
                        $tableBlueprint->index($column, "idx_{$table}_{$column}");
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $columns) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (is_array($column)) {
                        $name = implode('_', $column);
                        $tableBlueprint->dropIndex("idx_{$table}_{$name}");
                    } else {
                        $tableBlueprint->dropIndex("idx_{$table}_{$column}");
                    }
                }
            });
        }
    }
};
