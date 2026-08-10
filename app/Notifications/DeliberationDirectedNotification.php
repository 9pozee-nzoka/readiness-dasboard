<?php

namespace App\Notifications;

use App\Models\Deliberation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliberationDirectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Deliberation $deliberation,
        public readonly User $directedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->deliberation->event;

        return (new MailMessage)
            ->subject('You have been asked to review a deliberation')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->directedBy->name.' has directed a deliberation to you for review.')
            ->line('**Event:** '.$event->name)
            ->line('**Deliberation:** '.$this->deliberation->title)
            ->when($this->deliberation->recommendation, fn (MailMessage $mail) => $mail->line('**Recommendation:** '.str($this->deliberation->recommendation)->limit(200)))
            ->action('Review & Respond', route('deliberations.show', [$event, $this->deliberation]))
            ->salutation('Event Readiness Dashboard');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'deliberation_id' => $this->deliberation->id,
            'event_id' => $this->deliberation->event_id,
            'event_name' => $this->deliberation->event->name,
            'title' => $this->deliberation->title,
            'directed_by' => $this->directedBy->name,
            'message' => $this->directedBy->name.' asked you to review: '.$this->deliberation->title,
        ];
    }
}
