<?php

namespace App\Models;

use App\Enums\DecisionOutcome;
use App\Enums\DeliberationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deliberation extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'requirement_id',
        'type',
        'title',
        'description',
        'recommendation',
        'status',
        'outcome',
        'decision_due_date',
        'decision_made',
        'decision_notes',
        'approved_by',
        'approved_at',
        'created_by',
        'impact_level',
        'escalated_to',
        'escalated_at',
    ];

    protected function casts(): array
    {
        return [
            'decision_made' => 'boolean',
            'approved_at' => 'datetime',
            'escalated_at' => 'datetime',
            'decision_due_date' => 'date',
            'status' => DeliberationStatus::class,
            'outcome' => DecisionOutcome::class,
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function escalatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DeliberationComment::class)->orderBy('created_at');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'deliberation_participant')
            ->withPivot(['approved_at', 'rejected_at', 'notes', 'created_at'])
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DeliberationAttachment::class)->orderBy('created_at');
    }

    public function isOverdue(): bool
    {
        return $this->status->isOpen()
            && $this->decision_due_date !== null
            && $this->decision_due_date->isPast();
    }

    public function isConsultation(): bool
    {
        return $this->type === 'consultation';
    }

    public function isDeliberation(): bool
    {
        return $this->type === 'deliberation';
    }

    /** Badge classes for the type pill. */
    public function typeClasses(): array
    {
        return $this->isConsultation()
            ? ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'border' => 'border-sky-200']
            : ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200'];
    }

    public function isCritical(): bool
    {
        return $this->impact_level === 'critical';
    }

    public function canBeEscalated(): bool
    {
        return ! $this->status->isClosed();
    }

    public function markEscalated(User $escalatedTo): void
    {
        $this->update([
            'status' => DeliberationStatus::Escalated,
            'escalated_to' => $escalatedTo->id,
            'escalated_at' => now(),
        ]);
    }

    public function participantResponseFor(User $user): ?object
    {
        return $this->participants()
            ->where('user_id', $user->id)
            ->first()
            ?->pivot;
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function approvalSummary(): array
    {
        $participants = $this->participants;
        $total = $participants->count();
        $approved = $participants->whereNotNull('pivot.approved_at')->count();
        $rejected = $participants->whereNotNull('pivot.rejected_at')->count();
        $pending = $total - $approved - $rejected;

        return compact('approved', 'rejected', 'pending', 'total');
    }
}
