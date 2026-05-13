<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Event;
use App\Models\User;
use App\Notifications\IncompleteTasksReminderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    /**
     * Send a reminder to all employees in a department about their incomplete tasks.
     * Admin can send for any department; HOD can only send for their own.
     */
    public function send(Request $request, Event $event): RedirectResponse
    {
        $user = Auth::user();

        abort_unless($user->canSendReminders(), 403);

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        $departmentId = (int) $validated['department_id'];

        // HOD can only send for their own department
        if ($user->isHod() && $user->department_id !== $departmentId) {
            abort(403, 'You can only send reminders for your own department.');
        }

        $department = Department::findOrFail($departmentId);

        // Gather incomplete requirements for this department on this event
        $incompleteRequirements = $event->requirements()
            ->where('department_id', $departmentId)
            ->where('is_completed', false)
            ->get();

        if ($incompleteRequirements->isEmpty()) {
            return back()->with('info', 'All requirements for '.$department->name.' are already completed.');
        }

        // Notify all HOD and employees in this department
        $recipients = User::where('department_id', $departmentId)
            ->whereIn('role', ['hod', 'employee'])
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new IncompleteTasksReminderNotification(
                event: $event,
                department: $department,
                incompleteRequirements: $incompleteRequirements,
                sentBy: $user,
            ));
        }

        return back()->with('success', 'Reminder sent to '.$recipients->count().' member(s) of '.$department->name.'.');
    }
}
