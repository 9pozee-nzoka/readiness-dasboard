<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliberations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained('requirements')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->date('decision_due_date')->nullable();
            $table->boolean('decision_made')->default(false);
            $table->text('decision_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('impact_level')->default('medium'); // critical, medium, low
            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['decision_due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliberations');
    }
};
