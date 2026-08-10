<?php

namespace App\Notifications;

use App\Models\Requirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequirementAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Requirement $requirement,
        public User $assignedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Requirement Assigned: '.$this->requirement->description)
            ->line('You have been assigned a new requirement by '.$this->assignedBy->name.'.')
            ->line('Event: '.$this->requirement->event->name)
            ->line('Requirement: '.$this->requirement->description)
            ->line('Priority: '.$this->requirement->priority->label())
            ->action('View Event', url('/events/'.$this->requirement->event_id))
            ->line('Please review and update the status as you progress.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'You were assigned: '.$this->requirement->description,
            'event_name' => $this->requirement->event->name,
            'event_id' => $this->requirement->event_id,
            'requirement_id' => $this->requirement->id,
            'assigned_by' => $this->assignedBy->name,
        ];
    }
}
