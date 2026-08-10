<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningWeek extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'week_start',
        'week_end',
        'is_current',
        'campus',
        'schedule_reference',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Scope to the current week.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }
}
