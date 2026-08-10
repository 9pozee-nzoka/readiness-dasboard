<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('title');
            $table->date('meeting_date');
            $table->time('meeting_time')->nullable();
            $table->string('venue')->nullable();
            $table->text('minutes')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->timestamps();

            $table->index(['event_id', 'meeting_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
