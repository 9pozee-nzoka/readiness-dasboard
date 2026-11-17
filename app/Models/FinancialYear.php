<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_locked',
        'closed_at',
        'closed_by',
        'closure_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'is_locked' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function planningWeeks(): HasMany
    {
        return $this->hasMany(PlanningWeek::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    // ── Helper Methods ────────────────────────────────────────────

    /**
     * Check if a given date falls within this financial year.
     */
    public function containsDate(Carbon $date): bool
    {
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Get the financial year for a given date.
     * KSG FY runs July 1 - June 30.
     */
    public static function forDate(Carbon $date): ?self
    {
        return self::whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
    }

    /**
     * Get or create the financial year for a given date.
     */
    public static function getOrCreateForDate(Carbon $date): self
    {
        $existing = self::forDate($date);

        if ($existing) {
            return $existing;
        }

        // Calculate FY boundaries (July 1 - June 30)
        $year = $date->year;
        $month = $date->month;

        if ($month < 7) {
            // Jan-June: belongs to previous year's FY
            $startYear = $year - 1;
            $endYear = $year;
        } else {
            // July-Dec: belongs to current year's FY
            $startYear = $year;
            $endYear = $year + 1;
        }

        return self::create([
            'name' => $startYear.'/'.$endYear,
            'start_date' => Carbon::create($startYear, 7, 1),
            'end_date' => Carbon::create($endYear, 6, 30),
            'is_current' => false,
        ]);
    }

    /**
     * Mark this FY as current (unmark others).
     */
    public function markAsCurrent(): void
    {
        self::where('is_current', true)->update(['is_current' => false]);
        $this->update(['is_current' => true]);
    }

    /**
     * Close this financial year and lock it.
     */
    public function close(User $user, ?string $notes = null): void
    {
        $this->update([
            'is_locked' => true,
            'closed_at' => now(),
            'closed_by' => $user->id,
            'closure_notes' => $notes,
            'is_current' => false, // Cannot be current if closed
        ]);
    }

    /**
     * Reopen a closed financial year (admin override).
     */
    public function reopen(): void
    {
        $this->update([
            'is_locked' => false,
            'closed_at' => null,
            'closed_by' => null,
            'closure_notes' => null,
        ]);
    }

    /**
     * Generate planning weeks for this FY with auto-generated references.
     * Reference format: KSG/{CAMPUS}/{FY-CODE}/Week-{NUM}
     * Example: KSG/NRB/26-27/Week-001
     */
    public function generateWeeks(string $campus = 'NRB', bool $force = false): int
    {
        // Check if weeks already exist
        $existingCount = $this->planningWeeks()->where('campus', $campus)->count();

        if ($existingCount > 0 && ! $force) {
            return 0; // Already generated
        }

        if ($force && $existingCount > 0) {
            $this->planningWeeks()->where('campus', $campus)->delete();
        }

        // FY short code (e.g., "26-27" for 2026/2027)
        $fyCode = $this->getShortCode();

        // Start from the first Monday of the FY
        $current = $this->start_date->copy()->startOfWeek(Carbon::MONDAY);

        // If FY starts mid-week, adjust to the first full Monday-Sunday week
        if ($current->lt($this->start_date)) {
            $current->addWeek();
        }

        $weekNumber = 1;
        $created = 0;

        while ($current->lte($this->end_date)) {
            $weekStart = $current->copy();
            $weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY);

            // Don't exceed FY end date
            if ($weekEnd->gt($this->end_date)) {
                break;
            }

            // Generate reference: KSG/NRB/26-27/Week-001
            $reference = sprintf('KSG/%s/%s/Week-%03d', $campus, $fyCode, $weekNumber);

            // Generate label: 18-24 May 2026
            $label = $this->generateWeekLabel($weekStart, $weekEnd);

            PlanningWeek::create([
                'label' => $label,
                'financial_year' => $this->name,
                'week_number' => $weekNumber,
                'financial_year_id' => $this->id,
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'is_current' => false,
                'is_transition_week' => false,
                'campus' => $campus,
                'schedule_reference' => $reference,
            ]);

            $created++;
            $weekNumber++;
            $current->addWeek();
        }

        return $created;
    }

    /**
     * Extract FY short code from name (e.g., "2026/2027" → "26-27").
     */
    public function getShortCode(): string
    {
        // Match patterns like "2026/2027" or "2026-2027"
        if (preg_match('/(\d{2})(\d{2})[\/\-](\d{2})(\d{2})/', $this->name, $matches)) {
            return "{$matches[2]}-{$matches[4]}";
        }

        // Fallback: use last 2 digits of each year
        $parts = preg_split('/[\/\-]/', $this->name);
        if (count($parts) === 2) {
            return substr($parts[0], -2).'-'.substr($parts[1], -2);
        }

        return 'XX-XX';
    }

    /**
     * Generate week label: "18-24 May 2026" or "29 Jun - 05 Jul 2026".
     */
    private function generateWeekLabel(Carbon $start, Carbon $end): string
    {
        if ($start->month === $end->month) {
            // Same month: "18-24 May 2026"
            return sprintf(
                '%d-%d %s %d',
                $start->day,
                $end->day,
                $start->format('M'),
                $start->year
            );
        } else {
            // Crosses months: "29 Jun - 05 Jul 2026"
            return sprintf(
                '%d %s - %d %s %d',
                $start->day,
                $start->format('M'),
                $end->day,
                $end->format('M'),
                $end->year
            );
        }
    }

    /**
     * Get the week number for a date within this FY.
     */
    public function getWeekNumber(Carbon $date): int
    {
        if (! $this->containsDate($date)) {
            return 0;
        }

        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $fyStart = $this->start_date->copy()->startOfWeek(Carbon::MONDAY);

        $weeks = (int) ceil($fyStart->diffInDays($weekStart) / 7) + 1;

        return max(1, min(52, $weeks)); // Clamp between 1 and 52
    }

    /**
     * Generate display label.
     */
    public function label(): string
    {
        return 'FY '.$this->name;
    }
}
