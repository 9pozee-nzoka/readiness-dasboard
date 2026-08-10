<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'meeting_date',
        'meeting_time',
        'venue',
        'minutes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function agendaItems(): HasMany
    {
        return $this->hasMany(MeetingAgendaItem::class);
    }
}
