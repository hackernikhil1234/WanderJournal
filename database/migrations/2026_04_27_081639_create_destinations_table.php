<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country');
            $table->string('continent');
            $table->enum('category', ['beach', 'mountains', 'city', 'historical', 'wildlife', 'cultural', 'adventure', 'spiritual', 'island', 'desert']);
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('best_time_to_visit')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->decimal('avg_daily_budget', 10, 2)->nullable(); // in USD
            $table->string('currency')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('highlights')->nullable(); // top attractions JSON array
            $table->json('tags')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('popularity_score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
