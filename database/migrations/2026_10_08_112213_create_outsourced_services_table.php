<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hirings & Outsourced Services - Director/Senior Management visibility.
     * Per FR-19: Track external help/services used by departments.
     */
    public function up(): void
    {
        Schema::create('outsourced_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();

            // Service details
            $table->string('provider'); // ABC Events Ltd
            $table->string('service_type'); // External AV / PA Provider, Catering, Security, etc.
            $table->enum('type', ['outsourced_service', 'hiring'])->default('outsourced_service');
            $table->text('reason'); // Internal equipment unavailable

            // Dates
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Responsibility
            $table->foreignId('responsible_user_id')->constrained('users')->cascadeOnDelete();

            // Status tracking
            $table->enum('status', [
                'requested',
                'approved',
                'confirmed',
                'completed',
                'cancelled',
            ])->default('requested');

            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            // Cost
            $table->decimal('cost', 10, 2)->nullable(); // Estimated or approved amount

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['event_id', 'department_id']);
            $table->index(['status']);
            $table->index(['approval_status']);
            $table->index(['start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outsourced_services');
    }
};
