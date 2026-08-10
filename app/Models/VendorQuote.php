<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorQuote extends Model
{
    protected $fillable = [
        'vendor_id',
        'requirement_id',
        'amount',
        'details',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
