<?php

namespace App\Notifications;

use App\Models\Deliberation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliberationCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Deliberation $deliberation,
        public readonly User $createdBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->deliberation->event;
        $requirement = $this->deliberation->requirement;

        return (new MailMessage)
            ->subject('New Deliberation: '.$this->deliberation->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->createdBy->name.' has started a new deliberation for **'.$event->name.'**.')
            ->when($requirement, fn (MailMessage $mail) => $mail->line('**Linked requirement:** '.$requirement->description))
            ->line('**Impact level:** '.ucfirst($this->deliberation->impact_level))
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
            'created_by' => $this->createdBy->name,
            'message' => $this->createdBy->name.' started a deliberation: '.$this->deliberation->title,
        ];
    }
}
