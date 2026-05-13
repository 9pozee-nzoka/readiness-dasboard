<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'planning_week_id',
        'name',
        'type',
        'event_date',
        'event_time',
        'venue',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function planningWeek(): BelongsTo
    {
        return $this->belongsTo(PlanningWeek::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    /**
     * Overall readiness percentage across all departments.
     */
    public function overallReadiness(): int
    {
        $total = $this->requirements()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->requirements()->where('is_completed', true)->count();

        return (int) round(($completed / $total) * 100);
    }

    /**
     * Weighted readiness — critical tasks count more.
     */
    public function weightedReadiness(): int
    {
        $reqs = $this->requirements;

        if ($reqs->isEmpty()) {
            return 0;
        }

        $totalWeight     = $reqs->sum(fn ($r) => $r->priority->weight());
        $completedWeight = $reqs->where('is_completed', true)->sum(fn ($r) => $r->priority->weight());

        return $totalWeight > 0 ? (int) round(($completedWeight / $totalWeight) * 100) : 0;
    }

    /**
     * Count of unresolved critical requirements.
     */
    public function criticalPendingCount(): int
    {
        return $this->requirements()
            ->where('priority', \App\Enums\Priority::Critical->value)
            ->where('is_completed', false)
            ->count();
    }

    /**
     * Whether this event is "At Risk" — has multiple unresolved critical tasks.
     */
    public function isAtRisk(): bool
    {
        return $this->criticalPendingCount() >= 2;
    }

    /**
     * RAG status label based on overall readiness.
     */
    public function ragStatus(): string
    {
        return Department::ragStatus($this->overallReadiness());
    }
}
