<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutsourcedService extends Model
{
    protected $fillable = [
        'event_id',
        'department_id',
        'provider',
        'service_type',
        'type',
        'reason',
        'start_date',
        'end_date',
        'responsible_user_id',
        'status',
        'approval_status',
        'cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    // Status constants
    public const STATUS_REQUESTED = 'requested';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    // Approval status constants
    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REJECTED = 'rejected';

    // Type constants
    public const TYPE_OUTSOURCED_SERVICE = 'outsourced_service';

    public const TYPE_HIRING = 'hiring';

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Scope to pending approvals
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    /**
     * Scope to approved services
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    /**
     * Scope to active services (requested, approved, confirmed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_REQUESTED,
            self::STATUS_APPROVED,
            self::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Check if service is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    /**
     * Check if service is approved
     */
    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    /**
     * Approve the service
     */
    public function approve(): bool
    {
        return $this->update([
            'approval_status' => self::APPROVAL_APPROVED,
            'status' => self::STATUS_APPROVED,
        ]);
    }

    /**
     * Reject the service
     */
    public function reject(): bool
    {
        return $this->update([
            'approval_status' => self::APPROVAL_REJECTED,
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_COMPLETED => 'gray',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get approval status badge color
     */
    public function getApprovalColorAttribute(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_PENDING => 'yellow',
            self::APPROVAL_APPROVED => 'green',
            self::APPROVAL_REJECTED => 'red',
            default => 'gray',
        };
    }
}
