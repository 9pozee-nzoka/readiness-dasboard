<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_carry_forwards', function (Blueprint $table) {
            $table->id();

            // Source FY and destination FY
            $table->foreignId('from_financial_year_id')->constrained('financial_years')->cascadeOnDelete();
            $table->foreignId('to_financial_year_id')->constrained('financial_years')->cascadeOnDelete();

            // Link to original budget line (if budget_lines table exists)
            $table->foreignId('budget_line_id')->nullable()->constrained()->cascadeOnDelete();

            // Carry-forward details
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('original_amount', 15, 2); // Amount from previous FY
            $table->decimal('carried_forward_amount', 15, 2); // Amount approved for carry-forward
            $table->text('justification')->nullable(); // Why this budget needs to carry forward

            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'utilized'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at')->useCurrent();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Utilization tracking
            $table->decimal('utilized_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['from_financial_year_id', 'to_financial_year_id'], 'bcf_fy_index');
            $table->index(['status', 'approved_at'], 'bcf_status_approved_index');
            $table->index('department_id', 'bcf_department_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_carry_forwards');
    }
};
