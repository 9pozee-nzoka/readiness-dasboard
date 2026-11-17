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
        Schema::table('planning_weeks', function (Blueprint $table) {
            // Add foreign key to financial_years
            $table->foreignId('financial_year_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Mark transition weeks (final week spanning FY boundary)
            $table->boolean('is_transition_week')->default(false)->after('week_number');

            // Carry-forward status: null, pending, approved, rejected
            $table->enum('carry_forward_status', ['pending', 'approved', 'rejected'])->nullable()->after('is_transition_week');

            // Carry-forward approval tracking
            $table->foreignId('carry_forward_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('carry_forward_approved_at')->nullable();
            $table->text('carry_forward_notes')->nullable();

            $table->index(['financial_year_id', 'is_transition_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_weeks', function (Blueprint $table) {
            $table->dropForeign(['financial_year_id']);
            $table->dropForeign(['carry_forward_approved_by']);
            $table->dropIndex(['financial_year_id', 'is_transition_week']);
            $table->dropColumn([
                'financial_year_id',
                'is_transition_week',
                'carry_forward_status',
                'carry_forward_approved_by',
                'carry_forward_approved_at',
                'carry_forward_notes',
            ]);
        });
    }
};
