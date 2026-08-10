<?php

namespace App\Enums;

enum RequirementStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case UnderDeliberation = 'under_deliberation';
    case AwaitingApproval = 'awaiting_approval';
    case Approved = 'approved';
    case ProcurementOngoing = 'procurement_ongoing';
    case Blocked = 'blocked';
    case Escalated = 'escalated';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::InProgress => 'In Progress',
            self::UnderDeliberation => 'Under Deliberation',
            self::AwaitingApproval => 'Awaiting Approval',
            self::Approved => 'Approved',
            self::ProcurementOngoing => 'Procurement Ongoing',
            self::Blocked => 'Blocked',
            self::Escalated => 'Escalated',
            self::Completed => 'Completed',
        };
    }

    /**
     * Weight used in readiness calculation.
     * 1.0 = fully complete, 0.0 = not started.
     */
    public function readinessWeight(): float
    {
        return match ($this) {
            self::Completed => 1.0,
            self::Approved => 0.8,
            self::ProcurementOngoing => 0.6,
            self::InProgress => 0.5,
            self::AwaitingApproval => 0.3,
            self::UnderDeliberation => 0.2,
            self::Blocked => 0.1,
            self::Escalated => 0.1,
            self::NotStarted => 0.0,
        };
    }

    /**
     * Whether this status counts as "completed" for backward compatibility.
     */
    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    /**
     * Whether this status is "escalated".
     */
    public function isEscalated(): bool
    {
        return $this === self::Escalated;
    }

    /**
     * Whether this status is a "decision" state (approved or completed).
     */
    public function isDecided(): bool
    {
        return in_array($this, [self::Approved, self::Completed]);
    }

    /**
     * Tailwind CSS classes for the status badge.
     *
     * @return array{bg: string, text: string, border: string}
     */
    public function classes(): array
    {
        return match ($this) {
            self::Completed => ['bg' => 'bg-green-100',    'text' => 'text-green-700',    'border' => 'border-green-200'],
            self::Approved => ['bg' => 'bg-emerald-100',  'text' => 'text-emerald-700',  'border' => 'border-emerald-200'],
            self::ProcurementOngoing => ['bg' => 'bg-blue-100',     'text' => 'text-blue-700',     'border' => 'border-blue-200'],
            self::InProgress => ['bg' => 'bg-amber-100',    'text' => 'text-amber-700',    'border' => 'border-amber-200'],
            self::AwaitingApproval => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-200'],
            self::UnderDeliberation => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200'],
            self::Blocked => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'border' => 'border-gray-200'],
            self::Escalated => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'border' => 'border-red-200'],
            self::NotStarted => ['bg' => 'bg-gray-50',    'text' => 'text-gray-400',   'border' => 'border-gray-200'],
        };
    }

    /** Sort order — higher = more progressed. */
    public function sortOrder(): int
    {
        return match ($this) {
            self::Completed => 9,
            self::Approved => 8,
            self::ProcurementOngoing => 7,
            self::InProgress => 6,
            self::AwaitingApproval => 5,
            self::UnderDeliberation => 4,
            self::Blocked => 3,
            self::Escalated => 2,
            self::NotStarted => 1,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
