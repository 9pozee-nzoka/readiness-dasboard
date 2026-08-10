<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'category',
        'rating',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(VendorQuote::class);
    }
}
