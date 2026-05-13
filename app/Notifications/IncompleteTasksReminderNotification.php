<?php

namespace App\Notifications;

use App\Models\Department;
use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncompleteTasksReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Event $event,
        public readonly Department $department,
        public readonly Collection $incompleteRequirements,
        public readonly User $sentBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Reminder: Incomplete Readiness Tasks — '.$this->event->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('This is a reminder that the following readiness tasks for **'.$this->department->name.'** are still incomplete for the upcoming event:')
            ->line('**Event:** '.$this->event->name)
            ->line('**Date:** '.$this->event->event_date->format('l, d F Y'))
            ->line('**Venue:** '.($this->event->venue ?? 'TBC'))
            ->line('---')
            ->line('**Incomplete Tasks ('.count($this->incompleteRequirements).'):**');

        foreach ($this->incompleteRequirements as $req) {
            $officer = $req->responsible_officer ? ' (Officer: '.$req->responsible_officer.')' : '';
            $mail->line('• '.$req->description.$officer);
        }

        return $mail
            ->line('---')
            ->action('View Event Readiness', route('events.show', $this->event))
            ->line('Please ensure all tasks are completed before the event date.')
            ->salutation('Sent by '.$this->sentBy->name.' | Event Readiness Dashboard');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event_id'         => $this->event->id,
            'event_name'       => $this->event->name,
            'event_date'       => $this->event->event_date->format('d M Y'),
            'department_id'    => $this->department->id,
            'department_name'  => $this->department->name,
            'incomplete_count' => count($this->incompleteRequirements),
            'sent_by'          => $this->sentBy->name,
            'message'          => count($this->incompleteRequirements).' incomplete task(s) for '.$this->event->name,
        ];
    }
}
