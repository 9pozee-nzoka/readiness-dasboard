<?php

namespace App\Notifications;

use App\Models\Deliberation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliberationEscalatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Deliberation $deliberation,
        public readonly User $escalatedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->deliberation->event;

        return (new MailMessage)
            ->subject('Deliberation escalated: '.$this->deliberation->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->escalatedBy->name.' has escalated the deliberation **'.$this->deliberation->title.'**.')
            ->line('Please review and provide guidance or reassign ownership.')
            ->when($this->deliberation->decision_due_date, fn (MailMessage $mail) => $mail->line('**Decision due:** '.$this->deliberation->decision_due_date->format('d M Y')))
            ->action('View Deliberation', route('deliberations.show', [$event, $this->deliberation]))
            ->salutation('Event Readiness Dashboard');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'deliberation_id' => $this->deliberation->id,
            'event_id' => $this->deliberation->event_id,
            'event_name' => $this->deliberation->event->name,
            'title' => $this->deliberation->title,
            'escalated_by' => $this->escalatedBy->name,
            'message' => $this->escalatedBy->name.' escalated '.$this->deliberation->title,
        ];
    }
}
