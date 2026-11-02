<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAccommodation extends Model
{
    protected $table = 'event_accommodation';

    protected $fillable = [
        'event_id',
        'hostel_id',
        'residency_type',
        'rooms_booked',
        'rooms_available_after_booking',
        'requires_outsourcing',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rooms_booked' => 'integer',
            'rooms_available_after_booking' => 'integer',
            'requires_outsourcing' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }
}
