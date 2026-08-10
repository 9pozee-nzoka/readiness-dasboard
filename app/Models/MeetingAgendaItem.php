<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAgendaItem extends Model
{
    protected $fillable = [
        'meeting_id',
        'requirement_id',
        'topic',
        'decision',
        'status',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
