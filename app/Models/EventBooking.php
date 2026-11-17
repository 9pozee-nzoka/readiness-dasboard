<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'status',
        'event_id',
        'organization_name',
        'organization_category',
        'contact_person_name',
        'contact_person_designation',
        'contact_email',
        'contact_phone',
        'contact_phone_alt',
        'postal_address',
        'physical_address',
        'country',
        'kra_pin',
        'organization_website',
        'event_title',
        'event_category',
        'event_description',
        'event_purpose',
        'event_type',
        'expected_participants',
        'number_of_facilitators',
        'target_audience',
        'is_residential',
        'event_visibility',
        'vip_attendance',
        'preferred_start_date',
        'preferred_end_date',
        'daily_start_time',
        'daily_end_time',
        'alternative_start_date',
        'alternative_end_date',
        'setup_date',
        'dismantling_date',
        'is_recurring',
        'recurrence_details',
        'preferred_venue',
        'allocated_venue',
        'alternative_venue',
        'seating_arrangement',
        'number_of_meeting_rooms',
        'accommodation_required',
        'accommodation_rooms',
        'accommodation_nights',
        'catering_required',
        'catering_package',
        'special_dietary_requirements',
        'parking_spaces',
        'equipment_requirements',
        'catering_details',
        'estimated_cost',
        'quotation_breakdown',
        'quotation_sent_at',
        'deposit_amount',
        'deposit_paid',
        'deposit_paid_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'rejection_reason',
        'client_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_residential' => 'boolean',
            'vip_attendance' => 'boolean',
            'is_recurring' => 'boolean',
            'accommodation_required' => 'boolean',
            'catering_required' => 'boolean',
            'equipment_requirements' => 'array',
            'catering_details' => 'array',
            'preferred_start_date' => 'date',
            'preferred_end_date' => 'date',
            'alternative_start_date' => 'date',
            'alternative_end_date' => 'date',
            'setup_date' => 'date',
            'dismantling_date' => 'date',
            'quotation_sent_at' => 'datetime',
            'deposit_paid' => 'boolean',
            'deposit_paid_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    // Relationships
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Methods
    public static function generateBookingReference(): string
    {
        $prefix = 'KSG-BK-';
        $year = now()->format('y');
        $month = now()->format('m');

        // Get last booking number for this month
        $lastBooking = self::where('booking_reference', 'like', "{$prefix}{$year}{$month}%")
            ->orderByDesc('id')
            ->first();

        if ($lastBooking) {
            $lastNumber = (int) substr($lastBooking->booking_reference, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}{$year}{$month}-{$newNumber}";
    }

    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function reject(User $reviewer, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'under_review' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'confirmed' => 'bg-purple-100 text-purple-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
