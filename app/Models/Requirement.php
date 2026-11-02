<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\RequirementStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'department_id',
        'description',
        'priority',
        'status',
        'deadline',
        'is_completed',
        'responsible_officer',
        'assigned_to',
        'requires_deliberation',
        'completed_at',
        'is_escalated',
        'escalated_at',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'is_escalated' => 'boolean',
            'completed_at' => 'datetime',
            'escalated_at' => 'datetime',
            'deadline' => 'date',
            'priority' => Priority::class,
            'status' => RequirementStatus::class,
            'requires_deliberation' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(RequirementDependency::class, 'requirement_id');
    }

    public function vendorQuotes(): HasMany
    {
        return $this->hasMany(VendorQuote::class);
    }

    // ── Status helpers ────────────────────────────────────────────

    public function markCompleted(): void
    {
        $this->update([
            'status' => RequirementStatus::Completed,
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function markPending(): void
    {
        $this->update([
            'status' => RequirementStatus::NotStarted,
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function escalate(): void
    {
        $this->update([
            'is_escalated' => true,
            'escalated_at' => now(),
        ]);
    }

    public function setStatus(RequirementStatus $status): void
    {
        $this->update([
            'status' => $status,
            'is_completed' => $status->isCompleted(),
            'is_escalated' => $status->isEscalated(),
            'completed_at' => $status->isCompleted() ? now() : null,
        ]);
    }

    // ── Verification helpers ──────────────────────────────────────

    public function verify(User $user, ?string $notes = null): void
    {
        $this->update([
            'is_verified' => true,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);

        // Send notification to assigned user
        if ($this->assignedTo) {
            Notification::notify(
                userId: $this->assignedTo->id,
                type: 'verification_approved',
                body: "Your requirement \"{$this->description}\" has been verified by {$user->name}",
                actionUrl: route('events.show', $this->event),
                eventId: $this->event_id,
                requirementId: $this->id,
                title: 'Requirement Verified'
            );
        }
    }

    public function unverify(): void
    {
        $this->update([
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'verification_notes' => null,
        ]);
    }

    public function needsVerification(): bool
    {
        return $this->is_completed && ! $this->is_verified;
    }

    public function countsTowardReadiness(): bool
    {
        // Per October 2026 spec: only verified completed requirements count
        return $this->is_completed && $this->is_verified;
    }

    // ── Computed helpers ──────────────────────────────────────────

    public function isOverdue(): bool
    {
        return ! $this->status->isCompleted()
            && $this->deadline !== null
            && $this->deadline->isPast();
    }

    public function isCriticalAndPending(): bool
    {
        return $this->priority === Priority::Critical && ! $this->status->isCompleted();
    }

    public function isHighAndOverdue(): bool
    {
        return $this->priority === Priority::High
            && ! $this->status->isCompleted()
            && $this->deadline !== null
            && $this->deadline->diffInHours(now(), false) >= 24;
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', '!=', RequirementStatus::Completed->value);
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('priority', Priority::Critical->value);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', RequirementStatus::Completed->value)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString());
    }

    public function scopeByPriority(Builder $query): Builder
    {
        $orderCases = collect(Priority::cases())
            ->map(fn (Priority $priority): string => sprintf(
                "WHEN '%s' THEN %d",
                $priority->value,
                $priority->sortOrder(),
            ))
            ->implode(' ');

        return $query->orderByRaw("CASE priority {$orderCases} ELSE 99 END");
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeNeedsVerification(Builder $query): Builder
    {
        return $query->where('is_completed', true)
            ->where('is_verified', false);
    }
}
