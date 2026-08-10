<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringIssueOccurrence extends Model
{
    protected $fillable = [
        'recurring_issue_id',
        'event_id',
        'post_event_review_id',
        'notes',
        'source',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function recurringIssue(): BelongsTo
    {
        return $this->belongsTo(RecurringIssue::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function postEventReview(): BelongsTo
    {
        return $this->belongsTo(PostEventReview::class);
    }
}
