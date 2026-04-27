<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['flight', 'hotel', 'activity', 'car_rental', 'cruise', 'tour']);
            $table->string('title');
            $table->string('provider')->nullable();
            $table->string('booking_ref')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->text('details')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('confirmed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
