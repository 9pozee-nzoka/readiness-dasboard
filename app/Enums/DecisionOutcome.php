<?php

namespace App\Enums;

enum DecisionOutcome: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Deferred = 'deferred';
    case NoAction = 'no_action';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Deferred => 'Deferred',
            self::NoAction => 'No Action',
        };
    }

    /**
     * Maps the outcome to the resulting requirement status.
     */
    public function requirementStatus(): RequirementStatus
    {
        return match ($this) {
            self::Approved => RequirementStatus::Approved,
            self::Rejected, self::NoAction => RequirementStatus::Blocked,
            self::Deferred => RequirementStatus::AwaitingApproval,
        };
    }

    /**
     * Tailwind CSS classes for the outcome badge.
     *
     * @return array{bg: string, text: string, border: string}
     */
    public function classes(): array
    {
        return match ($this) {
            self::Approved => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200'],
            self::Rejected => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-200'],
            self::Deferred => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
            self::NoAction => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'border' => 'border-gray-200'],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $outcome) => [$outcome->value => $outcome->label()])
            ->all();
    }
}
