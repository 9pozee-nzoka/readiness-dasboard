<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_event_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->text('what_went_well')->nullable();
            $table->text('challenges')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_event_reviews');
    }
};
