<?php

namespace App\Notifications;

use App\Models\Deliberation;
use App\Models\DeliberationComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliberationCommentedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Deliberation $deliberation,
        public readonly DeliberationComment $comment,
        public readonly User $commentedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->deliberation->event;

        return (new MailMessage)
            ->subject('New comment on: '.$this->deliberation->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->commentedBy->name.' commented on the deliberation **'.$this->deliberation->title.'**.')
            ->line('**Comment:** '.str($this->comment->comment)->limit(200))
            ->action('View Discussion', route('deliberations.show', [$event, $this->deliberation]))
            ->salutation('Event Readiness Dashboard');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'deliberation_id' => $this->deliberation->id,
            'event_id' => $this->deliberation->event_id,
            'event_name' => $this->deliberation->event->name,
            'title' => $this->deliberation->title,
            'commented_by' => $this->commentedBy->name,
            'message' => $this->commentedBy->name.' commented on '.$this->deliberation->title,
        ];
    }
}
