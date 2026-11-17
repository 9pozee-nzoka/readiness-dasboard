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
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique(); // e.g., "2025/2026"
            $table->date('start_date'); // July 1, 2025
            $table->date('end_date'); // June 30, 2026
            $table->boolean('is_current')->default(false)->index();
            $table->boolean('is_locked')->default(false); // Locked against changes after closing
            $table->timestamp('closed_at')->nullable(); // When FY was officially closed
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('closure_notes')->nullable();
            $table->timestamps();

            // Ensure only one current FY at a time
            $table->index(['is_current', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};
