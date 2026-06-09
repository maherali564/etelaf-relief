<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_networks', function (Blueprint $table) {
            if (! Schema::hasColumn('crypto_networks', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('crypto_networks', 'contract_address')) {
                $table->string('contract_address')->nullable()->after('wallet_address');
            }
        });

        Schema::create('crypto_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_network_id')->constrained()->cascadeOnDelete();
            $table->string('txid')->unique();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->decimal('amount', 28, 8);
            $table->string('currency', 10);
            $table->foreignId('matched_donation_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_transactions');
        Schema::table('crypto_networks', function (Blueprint $table) {
            $table->dropColumn(['last_checked_at', 'contract_address']);
        });
    }
};
