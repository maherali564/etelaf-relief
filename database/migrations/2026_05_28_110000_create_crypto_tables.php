<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cryptocurrencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 10);
            $table->string('logo')->nullable();
            $table->decimal('min_amount', 16, 8)->default(0.00000000);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('crypto_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cryptocurrency_id')->constrained()->cascadeOnDelete();
            $table->string('network_name');
            $table->string('wallet_address');
            $table->string('qr_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_networks');
        Schema::dropIfExists('cryptocurrencies');
    }
};
