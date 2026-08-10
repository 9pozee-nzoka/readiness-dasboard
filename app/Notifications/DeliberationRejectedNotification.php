<?php

namespace App\Notifications;

use App\Models\Deliberation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliberationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Deliberation $deliberation,
        public readonly User $rejectedBy,
        public readonly ?string $notes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->deliberation->event;

        $mail = (new MailMessage)
            ->subject('Recommendation rejected: '.$this->deliberation->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->rejectedBy->name.' has rejected the recommendation for **'.$this->deliberation->title.'**.')
            ->action('View Deliberation', route('deliberations.show', [$event, $this->deliberation]));

        if ($this->notes) {
            $mail->line('**Notes:** '.str($this->notes)->limit(200));
        }

        return $mail->salutation('Event Readiness Dashboard');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'deliberation_id' => $this->deliberation->id,
            'event_id' => $this->deliberation->event_id,
            'event_name' => $this->deliberation->event->name,
            'title' => $this->deliberation->title,
            'rejected_by' => $this->rejectedBy->name,
            'message' => $this->rejectedBy->name.' rejected the recommendation for '.$this->deliberation->title,
        ];
    }
}
