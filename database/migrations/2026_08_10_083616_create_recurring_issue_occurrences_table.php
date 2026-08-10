<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_issue_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_event_review_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();    // what happened this time
            $table->string('source')->default('manual'); // manual | auto_review | deliberation
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['recurring_issue_id', 'occurred_at']);
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_issue_occurrences');
    }
};
