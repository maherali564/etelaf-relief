<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volunteers', function (Blueprint $table) {
            if (! Schema::hasColumn('volunteers', 'national_id')) {
                $table->string('national_id', 50)->nullable()->after('phone');
                $table->date('date_of_birth')->nullable()->after('national_id');
                $table->text('address')->nullable()->after('date_of_birth');
                $table->string('emergency_contact_name', 255)->nullable()->after('address');
                $table->string('emergency_contact_phone', 50)->nullable()->after('emergency_contact_name');
                $table->string('id_photo', 255)->nullable()->after('emergency_contact_phone');
                $table->text('notes')->nullable()->after('message');
                $table->timestamp('approved_at')->nullable()->after('notes');
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('rejected_at');
            }
        });

        Schema::create('volunteer_opportunities', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('location')->nullable();
            $table->integer('slots')->nullable();
            $table->integer('hours_required')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('volunteer_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_opportunity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('volunteer_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('assigned');
            $table->decimal('hours_logged', 8, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_tasks');
        Schema::dropIfExists('volunteer_opportunities');

        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'national_id', 'date_of_birth', 'address',
                'emergency_contact_name', 'emergency_contact_phone',
                'id_photo', 'notes', 'approved_at', 'rejected_at', 'reviewed_by',
            ]);
        });
    }
};
