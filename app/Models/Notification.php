<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'requirement_id',
        'comment_id',
        'type',
        'channel',
        'title',
        'body',
        'action_url',
        'read_at',
        'sent_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    // Notification types per FR-13
    public const TYPE_ASSIGNMENT = 'assignment';

    public const TYPE_DEADLINE_APPROACHING = 'deadline_approaching';

    public const TYPE_OVERDUE = 'overdue';

    public const TYPE_CRITICAL_INCOMPLETE = 'critical_incomplete';

    public const TYPE_VERIFICATION_REQUESTED = 'verification_requested';

    public const TYPE_VERIFIED = 'verified';

    public const TYPE_REJECTED = 'rejected';

    public const TYPE_EVIDENCE_UPLOADED = 'evidence_uploaded';

    public const TYPE_REASSIGNED = 'reassigned';

    public const TYPE_COMMENT_POSTED = 'comment_posted';

    public const TYPE_ESCALATED = 'escalated';

    public const TYPE_EVENT_CREATED = 'event_created';

    public const TYPE_EVENT_UPDATED = 'event_updated';

    public const TYPE_ATTENTION_REQUIRED = 'attention_required';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Create a notification for a user
     */
    public static function notify(
        int $userId,
        string $type,
        string $body,
        ?string $actionUrl = null,
        ?int $eventId = null,
        ?int $requirementId = null,
        ?int $commentId = null,
        ?string $title = null,
        string $channel = 'system'
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'event_id' => $eventId,
            'requirement_id' => $requirementId,
            'comment_id' => $commentId,
            'channel' => $channel,
            'status' => 'pending',
        ]);
    }

    /**
     * Notify multiple users at once
     */
    public static function notifyMany(
        array $userIds,
        string $type,
        string $body,
        ?string $actionUrl = null,
        ?int $eventId = null,
        ?int $requirementId = null,
        ?int $commentId = null,
        ?string $title = null,
        string $channel = 'system'
    ): void {
        foreach ($userIds as $userId) {
            self::notify($userId, $type, $body, $actionUrl, $eventId, $requirementId, $commentId, $title, $channel);
        }
    }
}
