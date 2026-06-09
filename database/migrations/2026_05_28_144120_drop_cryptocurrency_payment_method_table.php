<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCryptocurrencyPaymentMethodTable extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cryptocurrency_payment_method');
    }

    public function down(): void
    {
        Schema::create('cryptocurrency_payment_method', function (Blueprint $table) {
            $table->foreignId('cryptocurrency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->primary(['cryptocurrency_id', 'payment_method_id']);
        });
    }
}
