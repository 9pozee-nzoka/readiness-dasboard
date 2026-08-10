<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    protected $fillable = [
        'name',
        'type',
        'department_id',
        'event_id',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function latestMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->latest()->limit(1);
    }

    /** Tracks per-user read position. */
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_user')
            ->withPivot('last_read_message_id', 'last_read_at')
            ->withTimestamps();
    }

    /**
     * Count of messages this user hasn't read yet in this room.
     */
    public function unreadCountFor(User $user): int
    {
        $pivot = $this->readers()->where('user_id', $user->id)->first()?->pivot;
        $lastReadId = $pivot?->last_read_message_id ?? 0;

        return $this->messages()
            ->where('id', '>', $lastReadId)
            ->where('user_id', '!=', $user->id) // don't count own messages
            ->count();
    }

    /**
     * Mark all current messages in this room as read for the user.
     */
    public function markReadFor(User $user): void
    {
        $latestId = $this->messages()->latest('id')->value('id');
        if (! $latestId) {
            return;
        }

        $this->readers()->syncWithoutDetaching([
            $user->id => [
                'last_read_message_id' => $latestId,
                'last_read_at' => now(),
            ],
        ]);
    }

    /**
     * Whether the given user can access this room.
     */
    public function canAccess(User $user): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($this->type === 'department') {
            return $this->department_id === $user->department_id;
        }

        if ($this->type === 'event' && $this->event_id) {
            if ($user->isDepartmentScoped()) {
                return Requirement::where('event_id', $this->event_id)
                    ->where('department_id', $user->department_id)
                    ->exists();
            }
        }

        return true;
    }
}
