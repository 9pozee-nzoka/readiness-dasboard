<?php

namespace App\Mail;

use App\Models\PlanningWeek;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduleShared extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public PlanningWeek $planningWeek,
        public User $recipient,
        public User $sharedBy,
        public string $departmentName
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Weekly Schedule Shared: '.$this->planningWeek->label,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.schedule.shared',
            with: [
                'planningWeek' => $this->planningWeek,
                'recipient' => $this->recipient,
                'sharedBy' => $this->sharedBy,
                'departmentName' => $this->departmentName,
                'events' => $this->planningWeek->events,
                'actionUrl' => route('dashboard.index'),
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
        // Generate PDF schedule
        $pdf = Pdf::loadView('pdfs.schedule', [
            'planningWeek' => $this->planningWeek,
            'events' => $this->planningWeek->events,
            'departmentName' => $this->departmentName,
        ]);

        $fileName = 'schedule-'.str_replace(' ', '-', strtolower($this->planningWeek->label)).'.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $fileName)
                ->withMime('application/pdf'),
        ];
    }
}
