<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningWeek extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'financial_year',
        'week_number',
        'financial_year_id',
        'week_start',
        'week_end',
        'is_current',
        'is_transition_week',
        'carry_forward_status',
        'carry_forward_approved_by',
        'carry_forward_approved_at',
        'carry_forward_notes',
        'campus',
        'schedule_reference',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'is_current' => 'boolean',
            'is_transition_week' => 'boolean',
            'carry_forward_approved_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function carryForwardApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'carry_forward_approved_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function weeklyAccommodation(): HasMany
    {
        return $this->hasMany(WeeklyAccommodation::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeForFinancialYear(Builder $query, int $financialYearId): Builder
    {
        return $query->where('financial_year_id', $financialYearId);
    }

    public function scopeTransitionWeeks(Builder $query): Builder
    {
        return $query->where('is_transition_week', true);
    }

    // ── Financial Year Logic ──────────────────────────────────────

    /**
     * Detect if this week spans an FY boundary.
     * Returns true if week_start and week_end fall in different FYs.
     */
    public function spansFinancialYearBoundary(): bool
    {
        $fyStart = FinancialYear::forDate($this->week_start);
        $fyEnd = FinancialYear::forDate($this->week_end);

        return $fyStart && $fyEnd && $fyStart->id !== $fyEnd->id;
    }

    /**
     * Assign this planning week to the appropriate FY.
     * Rule: If week spans FY boundary, assign to the PREVIOUS (outgoing) FY.
     */
    public function assignToFinancialYear(): void
    {
        if ($this->spansFinancialYearBoundary()) {
            // Transition week: assign to previous FY
            $fy = FinancialYear::forDate($this->week_start);
            $this->update([
                'financial_year_id' => $fy?->id,
                'is_transition_week' => true,
                'financial_year' => $fy?->name,
                'week_number' => $fy?->getWeekNumber($this->week_start),
            ]);
        } else {
            // Normal week: assign to containing FY
            $fy = FinancialYear::forDate($this->week_start);
            $this->update([
                'financial_year_id' => $fy?->id,
                'is_transition_week' => false,
                'financial_year' => $fy?->name,
                'week_number' => $fy?->getWeekNumber($this->week_start),
            ]);
        }
    }

    /**
     * Check if this week requires carry-forward approval.
     * True if it's a transition week with events carrying into new FY.
     */
    public function requiresCarryForwardApproval(): bool
    {
        return $this->is_transition_week
            && $this->carry_forward_status === null
            && $this->events()->whereDate('event_date', '>', $this->financialYear?->end_date ?? now())->exists();
    }

    /**
     * Request carry-forward approval for this transition week.
     */
    public function requestCarryForward(): void
    {
        if (! $this->is_transition_week) {
            return;
        }

        $this->update(['carry_forward_status' => 'pending']);
    }

    /**
     * Approve carry-forward for this transition week.
     */
    public function approveCarryForward(User $approver, ?string $notes = null): void
    {
        $this->update([
            'carry_forward_status' => 'approved',
            'carry_forward_approved_by' => $approver->id,
            'carry_forward_approved_at' => now(),
            'carry_forward_notes' => $notes,
        ]);
    }

    /**
     * Reject carry-forward for this transition week.
     */
    public function rejectCarryForward(User $approver, string $reason): void
    {
        $this->update([
            'carry_forward_status' => 'rejected',
            'carry_forward_approved_by' => $approver->id,
            'carry_forward_approved_at' => now(),
            'carry_forward_notes' => $reason,
        ]);
    }

    /**
     * Get FY display label for this week.
     */
    public function financialYearLabel(): string
    {
        return $this->financialYear?->label() ?? $this->financial_year ?? 'N/A';
    }

    /**
     * Get week display with FY context.
     */
    public function fullLabel(): string
    {
        $base = $this->label;
        $fy = $this->financialYearLabel();
        $weekNum = $this->week_number ? " (Week {$this->week_number})" : '';

        return "{$base} - {$fy}{$weekNum}";
    }

    /**
     * Get display label with week number prefix.
     * Format: "Week X - [Date Range]"
     */
    public function displayLabel(): string
    {
        if ($this->week_number) {
            return "Week {$this->week_number} - {$this->label}";
        }

        return $this->label;
    }

    /**
     * Check if today falls within this planning week.
     */
    public function containsToday(): bool
    {
        $today = now()->toDateString();

        return $today >= $this->week_start->toDateString()
            && $today <= $this->week_end->toDateString();
    }

    /**
     * Automatically detect and update the current week based on today's date.
     */
    public static function updateCurrentWeek(): void
    {
        // Clear all existing current flags
        self::where('is_current', true)->update(['is_current' => false]);

        // Find the week containing today
        $currentWeek = self::whereDate('week_start', '<=', now())
            ->whereDate('week_end', '>=', now())
            ->first();

        if ($currentWeek) {
            $currentWeek->update(['is_current' => true]);
        }
    }
}
