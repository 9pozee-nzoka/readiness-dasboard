<?php

namespace App\Enums;

enum RequirementStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::InProgress => 'In Progress',
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
            self::InProgress => 0.5,
            self::NotStarted => 0.0,
        };
    }

    /**
     * Whether this status counts as "completed".
     */
    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    /**
     * Whether this status is "escalated" (kept for backward compatibility).
     */
    public function isEscalated(): bool
    {
        return false;
    }

    /**
     * Tailwind CSS classes for the status badge.
     *
     * @return array{bg: string, text: string, border: string}
     */
    public function classes(): array
    {
        return match ($this) {
            self::Completed => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200'],
            self::InProgress => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
            self::NotStarted => ['bg' => 'bg-gray-50', 'text' => 'text-gray-400', 'border' => 'border-gray-200'],
        };
    }

    /** Sort order — higher = more progressed. */
    public function sortOrder(): int
    {
        return match ($this) {
            self::Completed => 3,
            self::InProgress => 2,
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
