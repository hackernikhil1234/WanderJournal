<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('category', [
                'accommodation', 'food', 'transport', 'activities',
                'shopping', 'health', 'communication', 'other'
            ])->default('other');
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->string('receipt_image')->nullable();
            $table->boolean('is_shared')->default(false); // split among travelers
            $table->timestamps();

            $table->index(['trip_id', 'expense_date']);
            $table->index(['user_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
