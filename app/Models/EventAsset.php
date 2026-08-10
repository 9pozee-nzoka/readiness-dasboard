<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAsset extends Model
{
    protected $fillable = [
        'event_id',
        'asset_id',
        'quantity_allocated',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity_allocated' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
