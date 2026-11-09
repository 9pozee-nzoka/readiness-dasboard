<?php

namespace App\Mail;

use App\Models\Requirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequirementUnassigned extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Requirement $requirement,
        public User $previousAssignee,
        public User $reassignedBy
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyToEmail = $this->reassignedBy->email;
        $replyToName = $this->reassignedBy->name;

        return new Envelope(
            subject: 'Requirement Unassigned',
            replyTo: [new Address($replyToEmail, $replyToName)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.requirements.unassigned',
            with: [
                'requirement' => $this->requirement,
                'previousAssignee' => $this->previousAssignee,
                'reassignedBy' => $this->reassignedBy,
                'event' => $this->requirement->event,
                'actionUrl' => route('events.show', $this->requirement->event_id),
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
