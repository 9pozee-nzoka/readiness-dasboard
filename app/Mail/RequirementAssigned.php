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

class RequirementAssigned extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Requirement $requirement,
        public User $assignee,
        public User $assignedBy
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Use the person who assigned as Reply-To so assignee can reply directly to them
        $replyToEmail = $this->assignedBy->email;
        $replyToName = $this->assignedBy->name;

        return new Envelope(
            subject: 'New Requirement Assigned to You',
            replyTo: [new Address($replyToEmail, $replyToName)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.requirements.assigned',
            with: [
                'requirement' => $this->requirement,
                'assignee' => $this->assignee,
                'assignedBy' => $this->assignedBy,
                'event' => $this->requirement->event,
                'actionUrl' => route('events.show', $this->requirement->event_id),
                'acknowledgeUrl' => \URL::signedRoute('requirements.acknowledge', [
                    'requirement' => $this->requirement->id,
                    'user' => $this->assignee->id,
                ]),
                'completeUrl' => \URL::signedRoute('requirements.quick-complete', [
                    'requirement' => $this->requirement->id,
                    'user' => $this->assignee->id,
                ]),
                'escalateUrl' => \URL::signedRoute('requirements.quick-escalate', [
                    'requirement' => $this->requirement->id,
                    'user' => $this->assignee->id,
                ]),
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
