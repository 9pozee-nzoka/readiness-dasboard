<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_bookings', function (Blueprint $table) {
            $table->id();

            // Booking reference
            $table->string('booking_reference')->unique();
            $table->string('status')->default('pending'); // pending, under_review, approved, rejected, confirmed, cancelled

            // Link to created event (after approval)
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();

            // Client information
            $table->string('organization_name');
            $table->string('organization_category');
            $table->string('contact_person_name');
            $table->string('contact_person_designation')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('contact_phone_alt')->nullable();
            $table->string('postal_address')->nullable();
            $table->text('physical_address')->nullable();
            $table->string('country')->default('Kenya');
            $table->string('kra_pin')->nullable();
            $table->string('organization_website')->nullable();

            // Event information
            $table->string('event_title');
            $table->string('event_category');
            $table->text('event_description');
            $table->text('event_purpose');
            $table->string('event_type');
            $table->integer('expected_participants');
            $table->integer('number_of_facilitators')->nullable();
            $table->string('target_audience')->nullable();
            $table->boolean('is_residential')->default(false);
            $table->string('event_visibility')->default('invitation_only'); // public, invitation_only
            $table->boolean('vip_attendance')->default(false);

            // Event dates
            $table->date('preferred_start_date');
            $table->date('preferred_end_date');
            $table->time('daily_start_time');
            $table->time('daily_end_time');
            $table->date('alternative_start_date')->nullable();
            $table->date('alternative_end_date')->nullable();
            $table->date('setup_date')->nullable();
            $table->date('dismantling_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->text('recurrence_details')->nullable();

            // Venue and accommodation
            $table->string('preferred_venue')->nullable();
            $table->string('alternative_venue')->nullable();
            $table->string('seating_arrangement')->nullable();
            $table->integer('number_of_meeting_rooms')->nullable();
            $table->boolean('accommodation_required')->default(false);
            $table->integer('accommodation_rooms')->nullable();
            $table->integer('accommodation_nights')->nullable();
            $table->boolean('catering_required')->default(false);
            $table->string('catering_package')->nullable();
            $table->text('special_dietary_requirements')->nullable();
            $table->integer('parking_spaces')->nullable();

            // Equipment and technical requirements (JSON)
            $table->json('equipment_requirements')->nullable();

            // Catering details (JSON)
            $table->json('catering_details')->nullable();

            // Quotation and payment
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->text('quotation_breakdown')->nullable();
            $table->timestamp('quotation_sent_at')->nullable();
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->boolean('deposit_paid')->default(false);
            $table->timestamp('deposit_paid_at')->nullable();

            // Approval workflow
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Client tracking
            $table->foreignId('client_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('contact_email');
            $table->index(['preferred_start_date', 'preferred_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_bookings');
    }
};
