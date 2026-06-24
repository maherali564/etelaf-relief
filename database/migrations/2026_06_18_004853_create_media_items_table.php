<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('image'); // image, video
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_platform')->nullable(); // youtube, vimeo
            $table->string('thumbnail')->nullable();
            $table->json('title')->nullable();
            $table->json('description')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('sort_order');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
