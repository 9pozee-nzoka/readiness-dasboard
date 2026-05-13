<?php

namespace App\Models;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'department_id',
        'description',
        'priority',
        'deadline',
        'is_completed',
        'responsible_officer',
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
            'deadline'     => 'date',
            'priority'     => Priority::class,
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

    // ── Status helpers ────────────────────────────────────────────

    public function markCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function markPending(): void
    {
        $this->update([
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

    // ── Computed helpers ──────────────────────────────────────────

    public function isOverdue(): bool
    {
        return ! $this->is_completed
            && $this->deadline !== null
            && $this->deadline->isPast();
    }

    public function isCriticalAndPending(): bool
    {
        return $this->priority === Priority::Critical && ! $this->is_completed;
    }

    public function isHighAndOverdue(): bool
    {
        return $this->priority === Priority::High
            && ! $this->is_completed
            && $this->deadline !== null
            && $this->deadline->diffInHours(now(), false) >= 24;
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopePending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_completed', false);
    }

    public function scopeCritical(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('priority', Priority::Critical->value);
    }

    public function scopeOverdue(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_completed', false)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString());
    }

    public function scopeByPriority(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderByRaw("FIELD(priority, 'critical','high','medium','low')");
    }
}
