<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->after('is_admin');
                $table->boolean('is_active')->default(true)->after('role');
                $table->string('avatar')->nullable()->after('is_active');
                $table->string('preferred_locale', 5)->default('ar')->after('avatar');
                $table->string('phone')->nullable()->after('preferred_locale');
            });
        }

        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->json('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gaza_stats', function (Blueprint $table) {
            $table->id();
            $table->json('label');
            $table->string('value');
            $table->string('prefix')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->decimal('goal_amount', 14, 2)->default(0);
            $table->decimal('raised_amount', 14, 2)->default(0);
            $table->string('image')->nullable();
            $table->string('slug')->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('account_info')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('donor_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('USD');
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_interval')->nullable();
            $table->text('notes')->nullable();
            $table->string('locale', 5)->default('ar');
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('donor_name');
            $table->json('content');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('skills')->nullable();
            $table->text('availability')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->string('locale', 5)->default('ar');
            $table->timestamps();
        });

        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->boolean('is_subscribed')->default(true);
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('content')->nullable();
            $table->string('person_name')->nullable();
            $table->string('age')->nullable();
            $table->string('location')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->json('question');
            $table->json('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 10)->unique();
            $table->decimal('rate', 14, 6)->default(1);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('stories');
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('gaza_stats');
        Schema::dropIfExists('sliders');

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['role', 'is_active', 'avatar', 'preferred_locale', 'phone']);
            });
        }
    }
};
