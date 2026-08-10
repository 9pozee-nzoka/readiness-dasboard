<?php

namespace App\Http\Controllers;

use App\Models\Deliberation;
use App\Models\Event;
use App\Notifications\DeliberationApprovedNotification;
use App\Notifications\DeliberationRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliberationApprovalController extends Controller
{
    public function approve(Request $request, Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless(! $deliberation->status->isClosed(), 403, 'This deliberation is closed.');
        abort_unless($deliberation->hasParticipant(Auth::user()), 403, 'Only invited participants can approve this deliberation.');

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $deliberation->participants()->updateExistingPivot(Auth::id(), [
            'approved_at' => now(),
            'rejected_at' => null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->notifyCreator($deliberation, new DeliberationApprovedNotification($deliberation, Auth::user(), $validated['notes'] ?? null));

        return back()->with('success', 'Recommendation approved.');
    }

    public function reject(Request $request, Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless(! $deliberation->status->isClosed(), 403, 'This deliberation is closed.');
        abort_unless($deliberation->hasParticipant(Auth::user()), 403, 'Only invited participants can reject this deliberation.');

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $deliberation->participants()->updateExistingPivot(Auth::id(), [
            'approved_at' => null,
            'rejected_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->notifyCreator($deliberation, new DeliberationRejectedNotification($deliberation, Auth::user(), $validated['notes'] ?? null));

        return back()->with('success', 'Recommendation rejected.');
    }

    private function notifyCreator(Deliberation $deliberation, object $notification): void
    {
        $creator = $deliberation->createdBy;

        if ($creator && $creator->id !== Auth::id()) {
            $creator->notify($notification);
        }
    }
}
