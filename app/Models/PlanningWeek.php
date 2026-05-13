<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end'   => 'date',
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
    public function scopeCurrent(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_current', true);
    }
}
