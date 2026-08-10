<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Risk extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'description',
        'probability',
        'impact',
        'mitigation_plan',
        'owner_id',
        'status',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function riskScore(): int
    {
        $prob = match ($this->probability) {
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };
        $imp = match ($this->impact) {
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };

        return $prob * $imp;
    }
}
