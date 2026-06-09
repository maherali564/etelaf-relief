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
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->index('assigned_to');
            $table->index('status');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index('chat_session_id');
            $table->index('user_id');
            $table->index(['is_read', 'is_from_admin']);
        });

        Schema::table('volunteer_tasks', function (Blueprint $table) {
            $table->index('volunteer_opportunity_id');
            $table->index('volunteer_id');
            $table->index('assigned_by');
            $table->index('status');
        });

        Schema::table('crypto_transactions', function (Blueprint $table) {
            $table->index('crypto_network_id');
            $table->index('matched_donation_id');
            $table->index('status');
            $table->index('txid');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->index('post_id');
            $table->index('cryptocurrency_id');
            $table->index('crypto_network_id');
            $table->index('reviewed_by');
            $table->index('stripe_subscription_id');
            $table->index('billing_agreement_id');
            $table->index('idempotency_key');
        });

        Schema::table('volunteers', function (Blueprint $table) {
            $table->index('reviewed_by');
            $table->index('volunteer_opportunity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropIndex(['reviewed_by']);
            $table->dropIndex(['volunteer_opportunity_id']);
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
            $table->dropIndex(['cryptocurrency_id']);
            $table->dropIndex(['crypto_network_id']);
            $table->dropIndex(['reviewed_by']);
            $table->dropIndex(['stripe_subscription_id']);
            $table->dropIndex(['billing_agreement_id']);
            $table->dropIndex(['idempotency_key']);
        });

        Schema::table('crypto_transactions', function (Blueprint $table) {
            $table->dropIndex(['crypto_network_id']);
            $table->dropIndex(['matched_donation_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['txid']);
        });

        Schema::table('volunteer_tasks', function (Blueprint $table) {
            $table->dropIndex(['volunteer_opportunity_id']);
            $table->dropIndex(['volunteer_id']);
            $table->dropIndex(['assigned_by']);
            $table->dropIndex(['status']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['chat_session_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_read', 'is_from_admin']);
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['status']);
        });
    }
};
