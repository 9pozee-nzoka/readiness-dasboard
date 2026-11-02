<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_accommodation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('residency_type'); // Executive, Deluxe, Standard
            $table->integer('rooms_booked')->default(0);
            $table->integer('rooms_available_after_booking')->default(0); // Log remaining capacity
            $table->boolean('requires_outsourcing')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'hostel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_accommodation');
    }
};
