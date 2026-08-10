<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementDependency extends Model
{
    protected $fillable = [
        'requirement_id',
        'depends_on_requirement_id',
    ];

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(Requirement::class, 'depends_on_requirement_id');
    }
}
