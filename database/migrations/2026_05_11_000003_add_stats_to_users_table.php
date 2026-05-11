<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_money_spent', 15, 2)->default(0)->after('currency');
            $table->integer('total_days_traveled')->default(0)->after('total_money_spent');
            $table->json('visited_countries')->nullable()->after('total_days_traveled');
            $table->json('badges')->nullable()->after('visited_countries');
            $table->string('preferred_language', 5)->default('en')->after('badges');
            $table->boolean('dark_mode')->default(false)->after('preferred_language');
            $table->timestamp('last_active_at')->nullable()->after('dark_mode');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'total_money_spent', 'total_days_traveled', 'visited_countries',
                'badges', 'preferred_language', 'dark_mode', 'last_active_at',
            ]);
        });
    }
};
