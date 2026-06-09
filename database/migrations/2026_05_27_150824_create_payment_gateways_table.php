<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver'); // paypal, stripe, bank_transfer, manual, etc.
            $table->json('config')->nullable(); // API keys, merchant info, etc.
            $table->string('logo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (! Schema::hasColumn('payment_methods', 'gateway_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->foreignId('gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete()->after('id');
                $table->string('instructions')->nullable()->after('account_info');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_methods', 'gateway_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropConstrainedForeignId('gateway_id');
                $table->dropColumn('instructions');
            });
        }
        Schema::dropIfExists('payment_gateways');
    }
};
