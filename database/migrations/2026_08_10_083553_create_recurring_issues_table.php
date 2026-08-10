<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_issues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');        // department | logistics | technical | process | communication | other
            $table->string('severity')->default('medium'); // low | medium | high | critical
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->text('root_cause')->nullable();
            $table->text('recommended_fix')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'severity']);
            $table->index('is_resolved');
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_issues');
    }
};
