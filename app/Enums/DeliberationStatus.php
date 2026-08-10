<?php

namespace App\Enums;

enum DeliberationStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Escalated = 'escalated';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
            self::Escalated => 'Escalated',
        };
    }

    /**
     * Tailwind CSS classes for the status badge.
     *
     * @return array{bg: string, text: string, border: string}
     */
    public function classes(): array
    {
        return match ($this) {
            self::Open => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
            self::Closed => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200'],
            self::Escalated => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-200'],
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Open;
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }

    public function isEscalated(): bool
    {
        return $this === self::Escalated;
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
