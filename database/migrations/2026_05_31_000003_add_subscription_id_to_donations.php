<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('transaction_id');
        });
    }

    public function down()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('stripe_subscription_id');
        });
    }
};
