<?php

namespace App\Notifications;

use App\Models\Deliberation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliberationClosedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Deliberation $deliberation,
        public readonly User $closedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->deliberation->event;
        $outcome = $this->deliberation->outcome;

        $mail = (new MailMessage)
            ->subject('Deliberation closed: '.$this->deliberation->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->closedBy->name.' has closed the deliberation **'.$this->deliberation->title.'**.')
            ->when($outcome, fn (MailMessage $m) => $m->line('**Outcome:** '.$outcome->label()))
            ->line('**Decision notes:** '.str($this->deliberation->decision_notes ?? 'No notes provided')->limit(200))
            ->action('View Deliberation', route('deliberations.show', [$event, $this->deliberation]))
            ->salutation('Event Readiness Dashboard');

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'deliberation_id' => $this->deliberation->id,
            'event_id' => $this->deliberation->event_id,
            'event_name' => $this->deliberation->event->name,
            'title' => $this->deliberation->title,
            'outcome' => $this->deliberation->outcome?->value,
            'closed_by' => $this->closedBy->name,
            'message' => $this->closedBy->name.' closed '.$this->deliberation->title,
        ];
    }
}
