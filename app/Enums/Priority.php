<?php

namespace App\Enums;

enum Priority: string
{
    case Critical = 'critical';
    case High     = 'high';
    case Medium   = 'medium';
    case Low      = 'low';

    public function label(): string
    {
        return match ($this) {
            self::Critical => 'Critical',
            self::High     => 'High',
            self::Medium   => 'Medium',
            self::Low      => 'Low',
        };
    }

    /** Weight used in weighted readiness calculation. */
    public function weight(): int
    {
        return match ($this) {
            self::Critical => 5,
            self::High     => 3,
            self::Medium   => 2,
            self::Low      => 1,
        };
    }

    /**
     * Tailwind CSS classes for the priority badge.
     *
     * @return array{bg: string, text: string, border: string, dot: string}
     */
    public function classes(): array
    {
        return match ($this) {
            self::Critical => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'border' => 'border-red-200',    'dot' => 'bg-red-500'],
            self::High     => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'dot' => 'bg-orange-500'],
            self::Medium   => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'border' => 'border-amber-200',  'dot' => 'bg-amber-400'],
            self::Low      => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'border' => 'border-gray-200',   'dot' => 'bg-gray-400'],
        };
    }

    /** Sort order — lower = more urgent. */
    public function sortOrder(): int
    {
        return match ($this) {
            self::Critical => 1,
            self::High     => 2,
            self::Medium   => 3,
            self::Low      => 4,
        };
    }

    public static function options(): array
    {
        return array_map(fn ($p) => ['value' => $p->value, 'label' => $p->label()], self::cases());
    }
}
