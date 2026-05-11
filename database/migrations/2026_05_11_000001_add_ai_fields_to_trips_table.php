<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->boolean('ai_generated')->default(false)->after('is_public');
            $table->text('ai_summary')->nullable()->after('ai_generated');
            $table->string('food_preferences')->nullable()->after('ai_summary');
            $table->string('accommodation_type')->nullable()->after('food_preferences');
            $table->string('transportation_preference')->nullable()->after('accommodation_type');
            $table->enum('budget_mode', ['standard', 'budget_friendly'])->default('standard')->after('transportation_preference');
            $table->json('ai_metadata')->nullable()->after('budget_mode'); // stores tokens, model, etc.
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'ai_generated', 'ai_summary', 'food_preferences',
                'accommodation_type', 'transportation_preference',
                'budget_mode', 'ai_metadata',
            ]);
        });
    }
};
