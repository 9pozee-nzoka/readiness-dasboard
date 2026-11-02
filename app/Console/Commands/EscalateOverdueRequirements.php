<?php

namespace App\Console\Commands;

use App\Enums\Priority;
use App\Models\Notification;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EscalateOverdueRequirements extends Command
{
    protected $signature = 'requirements:escalate-overdue';

    protected $description = 'Auto-escalate overdue critical and high-priority requirements and notify up the chain';

    public function handle(): int
    {
        $escalated = 0;

        // Critical tasks overdue — notify coordinator/admin immediately
        $criticalOverdue = Requirement::with(['event', 'department'])
            ->where('is_completed', false)
            ->where('is_escalated', false)
            ->where('priority', Priority::Critical->value)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->get();

        foreach ($criticalOverdue as $req) {
            $req->escalate();

            // Notify all admins
            User::where('role', 'admin')->each(function (User $admin) use ($req) {
                Notification::notify(
                    $admin->id,
                    Notification::TYPE_ESCALATED,
                    "Critical requirement overdue: {$req->description} ({$req->event->name})",
                    route('events.show', $req->event_id),
                    $req->event_id,
                    $req->id
                );
            });

            // Notify director
            User::where('role', 'director')->each(function (User $director) use ($req) {
                Notification::notify(
                    $director->id,
                    Notification::TYPE_ESCALATED,
                    "Critical requirement overdue: {$req->description} ({$req->event->name})",
                    route('events.show', $req->event_id),
                    $req->event_id,
                    $req->id
                );
            });

            $escalated++;
        }

        // High-priority tasks overdue by 24+ hours — escalate to HOD
        $highOverdue = Requirement::with(['event', 'department'])
            ->where('is_completed', false)
            ->where('is_escalated', false)
            ->where('priority', Priority::High->value)
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->subDay()->toDateString())
            ->get();

        foreach ($highOverdue as $req) {
            $req->escalate();

            // Notify HOD of the department
            User::where('role', 'hod')
                ->where('department_id', $req->department_id)
                ->each(function (User $hod) use ($req) {
                    Notification::notify(
                        $hod->id,
                        Notification::TYPE_ESCALATED,
                        "High priority requirement overdue: {$req->description} ({$req->event->name})",
                        route('events.show', $req->event_id),
                        $req->event_id,
                        $req->id
                    );
                });

            $escalated++;
        }

        $this->info("Escalated {$escalated} overdue requirement(s).");
        Log::info("Auto-escalation: {$escalated} requirements escalated.");

        return self::SUCCESS;
    }
}
