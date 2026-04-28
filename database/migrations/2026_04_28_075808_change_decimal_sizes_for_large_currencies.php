<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('budget', 15, 2)->nullable()->change();
            $table->decimal('estimated_cost', 15, 2)->nullable()->change();
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable()->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('budget', 10, 2)->nullable()->change();
            $table->decimal('estimated_cost', 10, 2)->nullable()->change();
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->decimal('cost', 10, 2)->nullable()->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
    }
};
