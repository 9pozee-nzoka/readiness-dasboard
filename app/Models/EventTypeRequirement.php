<?php

namespace App\Models;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class EventTypeRequirement extends Model
{
    protected $fillable = [
        'event_type',
        'department_id',
        'description',
        'priority',
        'deadline',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => Priority::class,
            'deadline' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all active templates for a given event type, grouped by department.
     *
     * @return Collection<int, Collection>
     */
    public static function forType(string $eventType): Collection
    {
        return static::with('department')
            ->where('event_type', $eventType)
            ->where('is_active', true)
            ->orderBy('department_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('department_id');
    }

    /**
     * Save a new description to the template library for a type+department,
     * ignoring duplicates silently.
     */
    public static function remember(
        string $eventType,
        int $departmentId,
        string $description,
        string $priority = 'medium',
        ?string $deadline = null,
    ): void {
        static::firstOrCreate(
            [
                'event_type' => $eventType,
                'department_id' => $departmentId,
                'description' => $description,
            ],
            [
                'priority' => $priority,
                'deadline' => $deadline,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );
    }
}
