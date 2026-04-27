<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone')->nullable()->after('avatar');
            $table->string('country')->nullable()->after('phone');
            $table->string('bio', 500)->nullable()->after('country');
            $table->enum('travel_style', ['luxury', 'budget', 'adventure', 'cultural', 'backpacker', 'family'])->default('budget')->after('bio');
            $table->string('interests')->nullable()->after('travel_style'); // comma-separated: food,nature,history...
            $table->decimal('default_budget', 10, 2)->nullable()->after('interests');
            $table->string('currency', 3)->default('USD')->after('default_budget');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'phone', 'country', 'bio', 'travel_style', 'interests', 'default_budget', 'currency']);
        });
    }
};
