<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_accommodation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_week_id')->constrained('planning_weeks')->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->integer('occupancy')->default(0);
            $table->integer('rooms_out_of_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['planning_week_id', 'hostel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_accommodation');
    }
};
