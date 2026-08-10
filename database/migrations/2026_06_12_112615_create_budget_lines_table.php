<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('category');
            $table->text('description')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, approved, spent, variance
            $table->timestamps();

            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
