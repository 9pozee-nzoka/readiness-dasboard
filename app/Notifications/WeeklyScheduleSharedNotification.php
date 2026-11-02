<?php

namespace App\Notifications;

use App\Models\PlanningWeek;
use App\Models\User;
use App\Services\WeeklySchedulePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyScheduleSharedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PlanningWeek $planningWeek,
        public readonly User $sentBy,
        public readonly ?string $departmentName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $departmentPart = $this->departmentName
            ? "for the **{$this->departmentName}** Department"
            : 'for all departments';

        $eventCount = $this->planningWeek->events->count();
        $totalParticipants = $this->planningWeek->events->sum(fn ($e) => $e->totalParticipants());

        $pdfService = new WeeklySchedulePdfService;
        $pdfPath = $pdfService->generate($this->planningWeek);

        $grouped = $this->planningWeek->events->groupBy(fn ($e) => $e->schedule_category?->label() ?? 'Other Events');

        $actionUrl = route('reports.weekly-schedule', ['week' => $this->planningWeek->id]);

        $mail = (new MailMessage)
            ->subject('📅 Weekly Event Schedule — '.$this->planningWeek->label)
            ->view('emails.weekly-schedule', [
                'planningWeek' => $this->planningWeek,
                'recipient' => $notifiable,
                'sentBy' => $this->sentBy,
                'departmentPart' => $departmentPart,
                'eventCount' => $eventCount,
                'totalParticipants' => $totalParticipants,
                'grouped' => $grouped,
                'actionUrl' => $actionUrl,
            ])
            ->attach($pdfPath, [
                'as' => 'Weekly-Schedule-'.$this->planningWeek->label.'.pdf',
                'mime' => 'application/pdf',
            ]);

        // Clean up PDF after sending
        register_shutdown_function(function () use ($pdfPath) {
            if (file_exists($pdfPath)) {
                @unlink($pdfPath);
            }
        });

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'planning_week_id' => $this->planningWeek->id,
            'planning_week_label' => $this->planningWeek->label,
            'event_count' => $this->planningWeek->events->count(),
            'department_name' => $this->departmentName,
            'sent_by' => $this->sentBy->name,
            'message' => 'Weekly schedule shared: '.$this->planningWeek->label,
        ];
    }
}
