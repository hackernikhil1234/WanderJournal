<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('num_days');
            $table->decimal('budget', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('travel_style', ['luxury', 'budget', 'adventure', 'cultural', 'backpacker', 'family'])->default('budget');
            $table->string('interests')->nullable();
            $table->integer('num_travelers')->default(1);
            $table->enum('status', ['planning', 'confirmed', 'completed', 'cancelled'])->default('planning');
            $table->text('notes')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
