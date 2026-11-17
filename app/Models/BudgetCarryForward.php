<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetCarryForward extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_financial_year_id',
        'to_financial_year_id',
        'budget_line_id',
        'department_id',
        'event_id',
        'description',
        'original_amount',
        'carried_forward_amount',
        'justification',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'utilized_amount',
        'remaining_amount',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'carried_forward_amount' => 'decimal:2',
            'utilized_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function fromFinancialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class, 'from_financial_year_id');
    }

    public function toFinancialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class, 'to_financial_year_id');
    }

    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForFinancialYear($query, int $financialYearId)
    {
        return $query->where('to_financial_year_id', $financialYearId);
    }

    // ── Helper Methods ────────────────────────────────────────────

    /**
     * Approve this carry-forward request.
     */
    public function approve(User $approver, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'remaining_amount' => $this->carried_forward_amount,
        ]);
    }

    /**
     * Reject this carry-forward request.
     */
    public function reject(User $approver, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $reason,
        ]);
    }

    /**
     * Record utilization of carried forward budget.
     */
    public function recordUtilization(float $amount): void
    {
        $this->utilized_amount += $amount;
        $this->remaining_amount = $this->carried_forward_amount - $this->utilized_amount;

        if ($this->remaining_amount <= 0) {
            $this->status = 'utilized';
        }

        $this->save();
    }

    /**
     * Check if fully utilized.
     */
    public function isFullyUtilized(): bool
    {
        return $this->remaining_amount <= 0;
    }

    /**
     * Get utilization percentage.
     */
    public function utilizationPercentage(): int
    {
        if ($this->carried_forward_amount <= 0) {
            return 0;
        }

        return (int) round(($this->utilized_amount / $this->carried_forward_amount) * 100);
    }
}
