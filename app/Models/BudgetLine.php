<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLine extends Model
{
    protected $fillable = [
        'event_id',
        'category',
        'description',
        'estimated_cost',
        'actual_cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function variance(): float
    {
        return (float) $this->actual_cost - (float) $this->estimated_cost;
    }
}
