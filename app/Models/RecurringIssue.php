<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringIssue extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'severity',
        'department_id',
        'root_cause',
        'recommended_fix',
        'is_resolved',
        'resolved_at',
        'reported_by',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(RecurringIssueOccurrence::class)->orderByDesc('occurred_at');
    }

    public function occurrenceCount(): int
    {
        return $this->occurrences()->count();
    }

    /** How recently this occurred — for trend detection */
    public function lastOccurredAt(): ?Carbon
    {
        return $this->occurrences()->latest('occurred_at')->value('occurred_at')
            ? Carbon::parse($this->occurrences()->latest('occurred_at')->value('occurred_at'))
            : null;
    }

    /** Is this issue trending (occurred in the last 30 days)? */
    public function isTrending(): bool
    {
        return $this->occurrences()
            ->where('occurred_at', '>=', now()->subDays(30))
            ->exists();
    }

    public function severityClasses(): array
    {
        return match ($this->severity) {
            'critical' => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'border' => 'border-red-200',    'dot' => 'bg-red-500'],
            'high' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'dot' => 'bg-orange-500'],
            'medium' => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'border' => 'border-amber-200',  'dot' => 'bg-amber-400'],
            default => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'border' => 'border-gray-200',   'dot' => 'bg-gray-400'],
        };
    }

    public static function categories(): array
    {
        return [
            'department' => 'Department',
            'logistics' => 'Logistics',
            'technical' => 'Technical / ICT',
            'process' => 'Process',
            'communication' => 'Communication',
            'other' => 'Other',
        ];
    }
}
