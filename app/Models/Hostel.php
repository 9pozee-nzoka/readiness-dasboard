<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends Model
{
    protected $fillable = [
        'name',
        'residency_type',
        'capacity',
        'is_active',
        'display_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'capacity' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function weeklyAccommodation(): HasMany
    {
        return $this->hasMany(WeeklyAccommodation::class);
    }

    public function availableRooms(int $occupancy, int $outOfOrder): int
    {
        return max(0, $this->capacity - $occupancy - $outOfOrder);
    }
}
