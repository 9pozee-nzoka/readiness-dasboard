<?php

namespace App\Mail;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Comment $comment,
        public User $recipient,
        public string $notificationType
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->notificationType) {
            'new_comment' => 'New Comment on Your Requirement',
            'comment_reply' => 'Someone Replied to Your Comment',
            'hod_notification' => 'New Comment in Your Department',
            default => 'New Comment Notification',
        };

        // Use commenter's email as Reply-To so recipient can reply directly to them
        $replyToEmail = $this->comment->user->email;
        $replyToName = $this->comment->user->name;

        return new Envelope(
            subject: $subject,
            replyTo: [new Address($replyToEmail, $replyToName)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.comments.notification',
            with: [
                'comment' => $this->comment,
                'recipient' => $this->recipient,
                'notificationType' => $this->notificationType,
                'requirement' => $this->comment->requirement,
                'event' => $this->comment->requirement?->event ?? $this->comment->event,
                'actionUrl' => $this->comment->requirement_id
                    ? route('events.show', $this->comment->requirement->event_id)
                    : route('events.show', $this->comment->event_id),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
