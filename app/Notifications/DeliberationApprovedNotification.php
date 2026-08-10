<?php

namespace App\Notifications;

use App\Models\Deliberation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliberationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Deliberation $deliberation,
        public readonly User $approvedBy,
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
            ->subject('Recommendation approved: '.$this->deliberation->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->approvedBy->name.' has approved the recommendation for **'.$this->deliberation->title.'**.')
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
            'approved_by' => $this->approvedBy->name,
            'message' => $this->approvedBy->name.' approved the recommendation for '.$this->deliberation->title,
        ];
    }
}
