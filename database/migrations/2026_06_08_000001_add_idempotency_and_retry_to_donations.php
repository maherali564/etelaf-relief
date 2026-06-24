<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->unique()->after('notes');
            $table->unsignedTinyInteger('payment_attempts')->default(0)->after('rejection_reason');
            $table->text('last_error')->nullable()->after('payment_attempts');
            $table->timestamp('last_attempt_at')->nullable()->after('last_error');
        });

        Schema::table('payment_confirmations', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->unique()->after('reference_number');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['idempotency_key', 'payment_attempts', 'last_error', 'last_attempt_at']);
        });

        Schema::table('payment_confirmations', function (Blueprint $table) {
            $table->dropColumn(['idempotency_key']);
        });
    }
};
