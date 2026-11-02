<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyAccommodation extends Model
{
    protected $table = 'weekly_accommodation';

    protected $fillable = [
        'planning_week_id',
        'hostel_id',
        'occupancy',
        'rooms_out_of_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'occupancy' => 'integer',
            'rooms_out_of_order' => 'integer',
        ];
    }

    public function planningWeek(): BelongsTo
    {
        return $this->belongsTo(PlanningWeek::class);
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function availableRooms(): int
    {
        return $this->hostel->availableRooms($this->occupancy, $this->rooms_out_of_order);
    }
}
