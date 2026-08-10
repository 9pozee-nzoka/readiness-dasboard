<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'quantity',
        'is_available',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'quantity' => 'integer',
        ];
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_assets')
            ->withPivot('quantity_allocated', 'status')
            ->withTimestamps();
    }
}
