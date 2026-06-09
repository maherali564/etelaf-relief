<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->decimal('goal_amount', 14, 2)->default(0)->after('location');
            $table->decimal('raised_amount', 14, 2)->default(0)->after('goal_amount');
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['goal_amount', 'raised_amount']);
        });
    }
};
