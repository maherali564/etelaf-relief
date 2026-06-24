<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            if (! Schema::hasColumn('volunteers', 'volunteer_opportunity_id')) {
                $table->foreignId('volunteer_opportunity_id')->nullable()->after('reviewed_by')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropForeign(['volunteer_opportunity_id']);
            $table->dropColumn('volunteer_opportunity_id');
        });
    }
};
