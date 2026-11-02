<?php

namespace App\Models;

use App\Mail\CommentNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'event_id',
        'requirement_id',
        'user_id',
        'parent_comment_id',
        'channel',
        'message',
        'external_reference',
        'sender_identifier',
        'via_email',
        'directed_to',
        'directed_to_user_id',
    ];

    // Channels: system, email, whatsapp
    public const CHANNEL_SYSTEM = 'system';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function directedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'directed_to_user_id');
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id')->with('replies');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if this is a reply (has a parent)
     */
    public function isReply(): bool
    {
        return $this->parent_comment_id !== null;
    }

    /**
     * Check if this is a top-level comment
     */
    public function isTopLevel(): bool
    {
        return $this->parent_comment_id === null;
    }

    /**
     * Get all replies recursively
     */
    public function allReplies()
    {
        return $this->replies()->with('allReplies');
    }

    /**
     * Scope to only top-level comments (not replies)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_comment_id');
    }

    /**
     * Scope to only replies
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_comment_id');
    }

    /**
     * Create a comment and notify relevant users based on hierarchy and direction
     */
    public static function createWithNotification(array $data): self
    {
        $comment = self::create($data);
        $commenter = $comment->user;

        // Determine who to notify based on commenter's role and explicit direction
        if ($comment->requirement_id) {
            $requirement = $comment->requirement;
            $department = $requirement->department;

            // Check if comment is explicitly directed
            if ($comment->directed_to === 'specific_user' && $comment->directed_to_user_id) {
                // Directed to specific user
                self::notifySpecificUser($comment, $requirement);
            } elseif ($comment->directed_to === 'director') {
                // Explicitly directed to director
                self::notifyDirector($comment, $requirement);
            } elseif ($comment->directed_to === 'staff') {
                // Explicitly directed to staff/department
                self::notifyDepartmentStaff($comment, $requirement, $department);
            } else {
                // Auto (default hierarchical routing)
                if ($commenter->isEmployee()) {
                    // Staff comments → Notify HOD
                    self::notifyHOD($comment, $requirement, $department);
                } elseif ($commenter->isHOD()) {
                    // HOD comments → Default to Director (unless specified otherwise)
                    self::notifyDirector($comment, $requirement);
                } elseif ($commenter->isDirector()) {
                    // Director comments → Notify relevant HOD
                    self::notifyHOD($comment, $requirement, $department);
                } elseif ($commenter->isAdmin()) {
                    // Admin comments → Notify both HOD and Director
                    self::notifyHOD($comment, $requirement, $department);
                    self::notifyDirector($comment, $requirement);
                }
            }

            // Always notify the assigned officer if someone else commented
            if ($requirement->assigned_to && $comment->user_id !== $requirement->assigned_to) {
                self::notifyAssignedOfficer($comment, $requirement);
            }
        } elseif ($comment->event_id) {
            // Event-level comments - notify event stakeholders
            self::notifyEventStakeholders($comment);
        }

        // If it's a reply, notify the parent comment author
        if ($comment->parent_comment_id) {
            self::notifyParentCommentAuthor($comment);
        }

        return $comment;
    }

    /**
     * Notify a specific user
     */
    private static function notifySpecificUser(self $comment, Requirement $requirement): void
    {
        $targetUser = $comment->directedToUser;
        if (! $targetUser || $comment->user_id === $targetUser->id) {
            return;
        }

        Notification::notify(
            $targetUser->id,
            Notification::TYPE_COMMENT_POSTED,
            "{$comment->user->name} directed a comment to you: {$requirement->description}",
            route('events.show', $requirement->event_id),
            $requirement->event_id,
            $requirement->id,
            $comment->id,
            '🎯 Comment Directed to You'
        );

        // Send email notification
        if ($targetUser->email && self::isRealEmail($targetUser->email)) {
            \Mail::to($targetUser->email)->send(
                new CommentNotification($comment, $targetUser, 'new_comment')
            );
        }
    }

    /**
     * Notify department staff
     */
    private static function notifyDepartmentStaff(self $comment, Requirement $requirement, ?Department $department): void
    {
        if (! $department) {
            return;
        }

        // Get all employees and HOD in the department
        $staffMembers = User::where('department_id', $department->id)
            ->whereIn('role', ['employee', 'hod'])
            ->where('id', '!=', $comment->user_id)
            ->get();

        foreach ($staffMembers as $staff) {
            Notification::notify(
                $staff->id,
                Notification::TYPE_COMMENT_POSTED,
                "{$comment->user->name} commented on: {$requirement->description}",
                route('events.show', $requirement->event_id),
                $requirement->event_id,
                $requirement->id,
                $comment->id,
                '📢 Comment to Department'
            );

            // Send email notification
            if ($staff->email && self::isRealEmail($staff->email)) {
                \Mail::to($staff->email)->send(
                    new CommentNotification($comment, $staff, 'hod_notification')
                );
            }
        }
    }

    /**
     * Notify the HOD of the department
     */
    private static function notifyHOD(self $comment, Requirement $requirement, ?Department $department): void
    {
        if (! $department || ! $department->hod_id || $comment->user_id === $department->hod_id) {
            return;
        }

        $hod = User::find($department->hod_id);
        if (! $hod) {
            return;
        }

        $message = match ($comment->user->role->value) {
            'employee' => "{$comment->user->name} (Staff) commented on: {$requirement->description}",
            'director' => "Director {$comment->user->name} commented on: {$requirement->description}",
            'admin' => "Admin {$comment->user->name} commented on: {$requirement->description}",
            default => "{$comment->user->name} commented on: {$requirement->description}",
        };

        Notification::notify(
            $hod->id,
            Notification::TYPE_COMMENT_POSTED,
            $message,
            route('events.show', $requirement->event_id),
            $requirement->event_id,
            $requirement->id,
            $comment->id,
            '📨 New Comment from '.($comment->user->isEmployee() ? 'Staff' : $comment->user->role->value)
        );

        // Send email notification
        if ($hod->email && self::isRealEmail($hod->email)) {
            \Mail::to($hod->email)->send(
                new CommentNotification($comment, $hod, 'hod_notification')
            );
        }
    }

    /**
     * Notify the Director
     */
    private static function notifyDirector(self $comment, Requirement $requirement): void
    {
        $directors = User::where('role', 'director')->get();

        foreach ($directors as $director) {
            if ($comment->user_id === $director->id) {
                continue; // Don't notify self
            }

            $message = match ($comment->user->role->value) {
                'hod' => "HOD {$comment->user->name} from {$requirement->department->name} commented on: {$requirement->description}",
                'admin' => "Admin {$comment->user->name} commented on: {$requirement->description}",
                default => "{$comment->user->name} commented on: {$requirement->description}",
            };

            Notification::notify(
                $director->id,
                Notification::TYPE_COMMENT_POSTED,
                $message,
                route('events.show', $requirement->event_id),
                $requirement->event_id,
                $requirement->id,
                $comment->id,
                '📨 New Comment from '.($comment->user->isHOD() ? 'HOD' : $comment->user->role->value)
            );

            // Send email notification
            if ($director->email && self::isRealEmail($director->email)) {
                \Mail::to($director->email)->send(
                    new CommentNotification($comment, $director, 'hod_notification')
                );
            }
        }
    }

    /**
     * Notify the assigned officer
     */
    private static function notifyAssignedOfficer(self $comment, Requirement $requirement): void
    {
        $assignedUser = User::find($requirement->assigned_to);
        if (! $assignedUser) {
            return;
        }

        Notification::notify(
            $assignedUser->id,
            Notification::TYPE_COMMENT_POSTED,
            "{$comment->user->name} commented on your requirement: {$requirement->description}",
            route('events.show', $requirement->event_id),
            $requirement->event_id,
            $requirement->id,
            $comment->id,
            '📝 Comment on Your Requirement'
        );

        // Send email notification
        if ($assignedUser->email && self::isRealEmail($assignedUser->email)) {
            \Mail::to($assignedUser->email)->send(
                new CommentNotification($comment, $assignedUser, 'new_comment')
            );
        }
    }

    /**
     * Notify event stakeholders (for event-level comments)
     */
    private static function notifyEventStakeholders(self $comment): void
    {
        $event = $comment->event;

        // Notify all HODs and Directors about event-level comments
        $stakeholders = User::whereIn('role', ['hod', 'director'])
            ->where('id', '!=', $comment->user_id)
            ->get();

        foreach ($stakeholders as $stakeholder) {
            Notification::notify(
                $stakeholder->id,
                Notification::TYPE_COMMENT_POSTED,
                "{$comment->user->name} commented on event: {$event->name}",
                route('events.show', $event->id),
                $event->id,
                null,
                $comment->id,
                '📅 Comment on Event'
            );

            if ($stakeholder->email && self::isRealEmail($stakeholder->email)) {
                \Mail::to($stakeholder->email)->send(
                    new CommentNotification($comment, $stakeholder, 'hod_notification')
                );
            }
        }
    }

    /**
     * Notify the parent comment author (for replies)
     */
    private static function notifyParentCommentAuthor(self $comment): void
    {
        $parentComment = $comment->parentComment;
        if (! $parentComment || $parentComment->user_id === $comment->user_id) {
            return;
        }

        $parentUser = $parentComment->user;
        $actionUrl = $comment->requirement_id
            ? route('events.show', $comment->requirement->event_id)
            : route('events.show', $comment->event_id);

        Notification::notify(
            $parentUser->id,
            Notification::TYPE_COMMENT_POSTED,
            "{$comment->user->name} replied to your comment",
            $actionUrl,
            $comment->event_id,
            $comment->requirement_id,
            $comment->id,
            '↩️ Reply to Your Comment'
        );

        // Send email notification
        if ($parentUser->email && self::isRealEmail($parentUser->email)) {
            \Mail::to($parentUser->email)->send(
                new CommentNotification($comment, $parentUser, 'comment_reply')
            );
        }
    }

    /**
     * Check if email is real (not a seeded fake email)
     */
    private static function isRealEmail(string $email): bool
    {
        // Skip specific seeded fake email patterns
        $fakeEmails = [
            'ict-hod@ksg.ac.ke',
            'finance-hod@ksg.ac.ke',
            'administration-hod@ksg.ac.ke',
            'security-hod@ksg.ac.ke',
            'logistics-hod@ksg.ac.ke',
            'communications-hod@ksg.ac.ke',
            'facilities-hod@ksg.ac.ke',
            'human-resources-hod@ksg.ac.ke',
            'protocol-hod@ksg.ac.ke',
            'catering-hod@ksg.ac.ke',
            'kevin.omondi@ksg.ac.ke',
            'sarah.achieng@ksg.ac.ke',
            'paul.kiptoo@ksg.ac.ke',
            'director@ksg.ac.ke',
        ];

        $emailLower = strtolower($email);

        // Check if it's a known fake email
        if (in_array($emailLower, $fakeEmails)) {
            return false;
        }

        // Skip emails with test/fake/demo patterns
        $fakePatterns = [
            '@example.com',
            '@test.com',
            'fake',
            'test@',
            'demo@',
        ];

        foreach ($fakePatterns as $pattern) {
            if (str_contains($emailLower, $pattern)) {
                return false;
            }
        }

        return true;
    }
}
