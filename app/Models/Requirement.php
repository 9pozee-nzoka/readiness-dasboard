<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\RequirementStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function deliberation(): HasOne
    {
        return $this->hasOne(Deliberation::class);
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
            'status' => RequirementStatus::Escalated,
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
}
