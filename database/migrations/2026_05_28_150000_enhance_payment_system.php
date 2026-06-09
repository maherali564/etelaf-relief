<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_gateways', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('payment_gateways', 'type')) {
                $table->string('type')->default('traditional')->after('driver')
                    ->comment('traditional, crypto, bank_transfer');
            }
            if (! Schema::hasColumn('payment_gateways', 'supported_currencies')) {
                $table->json('supported_currencies')->nullable()->after('config');
            }
            if (! Schema::hasColumn('payment_gateways', 'min_amount')) {
                $table->decimal('min_amount', 14, 2)->nullable()->after('sort_order');
            }
            if (! Schema::hasColumn('payment_gateways', 'max_amount')) {
                $table->decimal('max_amount', 14, 2)->nullable()->after('min_amount');
            }
            if (! Schema::hasColumn('payment_gateways', 'processing_fee')) {
                $table->decimal('processing_fee', 5, 2)->nullable()->after('max_amount')
                    ->comment('Percentage fee charged by gateway');
            }
            if (! Schema::hasColumn('payment_gateways', 'webhook_url')) {
                $table->string('webhook_url')->nullable()->after('processing_fee');
            }
            if (! Schema::hasColumn('payment_gateways', 'payment_instructions')) {
                $table->json('payment_instructions')->nullable()->after('webhook_url');
            }
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_methods', 'name_translated')) {
                $table->json('name_translated')->nullable()->after('name');
            }
        });

        Schema::table('donations', function (Blueprint $table) {
            if (! Schema::hasColumn('donations', 'confirmation_details')) {
                $table->json('confirmation_details')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('donations', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('crypto_network_id');
            }
            if (! Schema::hasColumn('donations', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('donations', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reviewed_at');
            }
        });

        Schema::table('cryptocurrencies', function (Blueprint $table) {
            if (! Schema::hasColumn('cryptocurrencies', 'max_amount')) {
                $table->decimal('max_amount', 16, 8)->nullable()->after('min_amount');
            }
            if (! Schema::hasColumn('cryptocurrencies', 'processing_fee')) {
                $table->decimal('processing_fee', 5, 2)->nullable()->after('max_amount');
            }
            if (! Schema::hasColumn('cryptocurrencies', 'name_translated')) {
                $table->json('name_translated')->nullable()->after('name');
            }
        });

        Schema::table('crypto_networks', function (Blueprint $table) {
            if (! Schema::hasColumn('crypto_networks', 'min_amount')) {
                $table->decimal('min_amount', 16, 8)->nullable()->after('wallet_address');
            }
            if (! Schema::hasColumn('crypto_networks', 'processing_fee')) {
                $table->decimal('processing_fee', 5, 2)->nullable()->after('min_amount');
            }
            if (! Schema::hasColumn('crypto_networks', 'explorer_url')) {
                $table->string('explorer_url')->nullable()->after('qr_code');
            }
            if (! Schema::hasColumn('crypto_networks', 'notes')) {
                $table->text('notes')->nullable()->after('explorer_url');
            }
        });

        Schema::create('payment_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // bank_transfer, crypto
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_account')->nullable();
            $table->date('transfer_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('proof_document')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_confirmations');

        Schema::table('crypto_networks', function (Blueprint $table) {
            $table->dropColumn(['min_amount', 'processing_fee', 'explorer_url', 'notes']);
        });
        Schema::table('cryptocurrencies', function (Blueprint $table) {
            $table->dropColumn(['max_amount', 'processing_fee', 'name_translated']);
        });
        Schema::table('donations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['confirmation_details', 'reviewed_at', 'rejection_reason']);
        });
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('name_translated');
        });
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropColumn(['slug', 'type', 'supported_currencies', 'min_amount', 'max_amount', 'processing_fee', 'webhook_url', 'payment_instructions']);
        });
    }
};
