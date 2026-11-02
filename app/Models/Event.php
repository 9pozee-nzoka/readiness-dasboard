<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\RequirementStatus;
use App\Enums\ScheduleCategory;
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
        'schedule_category',
        'client',
        'event_date',
        'event_date_end',
        'event_time',
        'venue',
        'participants_residential',
        'participants_non_residential',
        'coordinator',
        'programme_assistant',
        'package_remarks',
        'schedule_reference',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'event_date_end' => 'date',
            'schedule_category' => ScheduleCategory::class,
            'participants_residential' => 'integer',
            'participants_non_residential' => 'integer',
        ];
    }

    public function totalParticipants(): ?int
    {
        if ($this->participants_residential === null && $this->participants_non_residential === null) {
            return null;
        }

        return (int) ($this->participants_residential ?? 0) + (int) ($this->participants_non_residential ?? 0);
    }

    public function dateRangeLabel(): string
    {
        if ($this->event_date_end === null || $this->event_date->isSameDay($this->event_date_end)) {
            return $this->event_date->format('d M Y');
        }

        return $this->event_date->format('d M').' – '.$this->event_date_end->format('d M Y');
    }

    public function planningWeek(): BelongsTo
    {
        return $this->belongsTo(PlanningWeek::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

    public function postEventReview(): HasMany
    {
        return $this->hasMany(PostEventReview::class);
    }

    public function eventAssets(): HasMany
    {
        return $this->hasMany(EventAsset::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(EventAccommodation::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function outsourcedServices(): HasMany
    {
        return $this->hasMany(OutsourcedService::class);
    }

    /**
     * Overall readiness percentage across all departments.
     * Per October 2026 spec: only verified completed requirements count toward readiness.
     */
    public function overallReadiness(): int
    {
        $reqs = $this->requirements;

        if ($reqs->isEmpty()) {
            return 0;
        }

        $totalWeight = $reqs->count();
        // Only count verified requirements (completed AND verified by HOD)
        $verifiedCount = $reqs->filter(fn ($r) => $r->countsTowardReadiness())->count();

        return (int) round(($verifiedCount / $totalWeight) * 100);
    }

    /**
     * Weighted readiness — critical tasks count more.
     * Per October 2026 spec: only verified completed requirements count toward readiness.
     */
    public function weightedReadiness(): int
    {
        $reqs = $this->requirements;

        if ($reqs->isEmpty()) {
            return 0;
        }

        $totalWeight = $reqs->sum(fn ($r) => $r->priority->weight());
        // Only count verified requirements
        $verifiedWeight = $reqs->filter(fn ($r) => $r->countsTowardReadiness())
            ->sum(fn ($r) => $r->priority->weight());

        return $totalWeight > 0 ? (int) round(($verifiedWeight / $totalWeight) * 100) : 0;
    }

    /**
     * Decision readiness — percentage of requirements that are completed.
     * (Simplified from old deliberation system)
     */
    public function decisionReadiness(): int
    {
        $reqs = $this->requirements;

        if ($reqs->isEmpty()) {
            return 0;
        }

        $completed = $reqs->filter(fn ($r) => $r->status->isCompleted())->count();

        return (int) round(($completed / $reqs->count()) * 100);
    }

    /**
     * Risk readiness — based on mitigated risks.
     */
    public function riskReadiness(): int
    {
        $risks = $this->risks;

        if ($risks->isEmpty()) {
            return 100;
        }

        $mitigated = $risks->where('status', 'mitigated')->count() + $risks->where('status', 'closed')->count();

        return (int) round(($mitigated / $risks->count()) * 100);
    }

    /**
     * Count of unresolved critical requirements.
     */
    public function criticalPendingCount(): int
    {
        return $this->requirements()
            ->where('priority', Priority::Critical->value)
            ->where('status', '!=', RequirementStatus::Completed->value)
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
     * Risk Score (0–100) — higher = more at risk.
     * Calculated from: overdue tasks, critical pending, unmitigated risks,
     * days remaining, and escalated requirements.
     */
    public function riskScore(): int
    {
        $reqs = $this->requirements;
        $total = $reqs->count();

        if ($total === 0) {
            return 0;
        }

        $score = 0;

        // Factor 1: Overall incompletion (max 30 pts)
        $readiness = $this->overallReadiness();
        $score += (int) round((1 - $readiness / 100) * 30);

        // Factor 2: Critical pending (max 30 pts — each critical = 6 pts, cap at 5)
        $criticalPending = $this->criticalPendingCount();
        $score += min(30, $criticalPending * 6);

        // Factor 3: Overdue requirements (max 20 pts)
        $overdue = $reqs->filter(fn ($r) => $r->isOverdue())->count();
        $score += min(20, (int) round(($overdue / $total) * 20));

        // Factor 4: Days remaining pressure (max 10 pts)
        $daysLeft = now()->diffInDays($this->event_date, false);
        if ($daysLeft <= 0) {
            $score += 10;
        } elseif ($daysLeft <= 2) {
            $score += 8;
        } elseif ($daysLeft <= 5) {
            $score += 4;
        } elseif ($daysLeft <= 7) {
            $score += 2;
        }

        // Factor 5: Escalated but unresolved (max 10 pts)
        $escalated = $reqs->where('is_escalated', true)
            ->filter(fn ($r) => ! in_array($r->status->value, ['completed', 'approved']))->count();
        $score += min(10, $escalated * 2);

        return min(100, $score);
    }

    /**
     * Risk level label from score.
     */
    public function riskLevel(): string
    {
        return match (true) {
            $this->riskScore() >= 70 => 'Critical',
            $this->riskScore() >= 45 => 'High',
            $this->riskScore() >= 20 => 'Medium',
            default => 'Low',
        };
    }

    /**
     * Tailwind classes for the risk level badge.
     *
     * @return array{bg: string, text: string, border: string}
     */
    public function riskLevelClasses(): array
    {
        return match ($this->riskLevel()) {
            'Critical' => ['bg' => 'bg-red-100',    'text' => 'text-red-800',    'border' => 'border-red-300'],
            'High' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-300'],
            'Medium' => ['bg' => 'bg-amber-100',  'text' => 'text-amber-800',  'border' => 'border-amber-300'],
            default => ['bg' => 'bg-green-100',  'text' => 'text-green-800',  'border' => 'border-green-300'],
        };
    }

    /**
     * RAG status label based on overall readiness.
     */
    public function ragStatus(): string
    {
        return Department::ragStatus($this->overallReadiness());
    }
}
