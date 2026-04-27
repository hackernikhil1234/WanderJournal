<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->integer('day_number');
            $table->date('date')->nullable();
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->string('theme_color', 7)->nullable()->default('#8B4513');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_days');
    }
};
