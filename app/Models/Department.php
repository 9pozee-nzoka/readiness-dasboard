<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'head_name',
        'head_contact',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    /**
     * Readiness percentage for this department on a given event.
     */
    public function readinessForEvent(int $eventId): int
    {
        $total = $this->requirements()->where('event_id', $eventId)->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->requirements()->where('event_id', $eventId)->where('is_completed', true)->count();

        return (int) round(($completed / $total) * 100);
    }

    /**
     * Weighted readiness percentage for this department on a given event.
     * Critical tasks carry more weight than low-priority ones.
     */
    public function weightedReadinessForEvent(int $eventId): int
    {
        $reqs = $this->requirements()->where('event_id', $eventId)->get();

        if ($reqs->isEmpty()) {
            return 0;
        }

        $totalWeight     = $reqs->sum(fn ($r) => $r->priority->weight());
        $completedWeight = $reqs->where('is_completed', true)->sum(fn ($r) => $r->priority->weight());

        return $totalWeight > 0 ? (int) round(($completedWeight / $totalWeight) * 100) : 0;
    }

    /**
     * RAG status label for a given readiness percentage.
     */
    public static function ragStatus(int $percentage): string
    {
        return match (true) {
            $percentage === 100 => 'Ready',
            $percentage > 0    => 'In Progress',
            default            => 'Not Started',
        };
    }

    /**
     * Tailwind colour classes for RAG status.
     *
     * @return array{bg: string, text: string, bar: string}
     */
    public static function ragClasses(int $percentage): array
    {
        return match (true) {
            $percentage === 100 => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'bar' => 'bg-green-500'],
            $percentage > 0    => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'bar' => 'bg-amber-400'],
            default            => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'bar' => 'bg-red-400'],
        };
    }
}
