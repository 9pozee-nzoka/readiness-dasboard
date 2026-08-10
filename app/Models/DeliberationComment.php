<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliberationComment extends Model
{
    protected $fillable = [
        'deliberation_id',
        'user_id',
        'comment',
    ];

    public function deliberation(): BelongsTo
    {
        return $this->belongsTo(Deliberation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
