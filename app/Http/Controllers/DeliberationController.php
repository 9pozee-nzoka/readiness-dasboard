<?php

namespace App\Http\Controllers;

use App\Enums\DecisionOutcome;
use App\Enums\DeliberationStatus;
use App\Enums\RequirementStatus;
use App\Enums\UserRole;
use App\Models\Deliberation;
use App\Models\DeliberationAttachment;
use App\Models\DeliberationComment;
use App\Models\Event;
use App\Models\Requirement;
use App\Models\User;
use App\Notifications\DeliberationClosedNotification;
use App\Notifications\DeliberationCommentedNotification;
use App\Notifications\DeliberationCreatedNotification;
use App\Notifications\DeliberationDirectedNotification;
use App\Notifications\DeliberationEscalatedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeliberationController extends Controller
{
    /**
     * Determine whether the authenticated user can create a deliberation.
     */
    private function canCreate(?Requirement $requirement = null): bool
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        // HOD can create deliberations for their own department
        if ($user->isHod()) {
            return $requirement === null || $requirement->department_id === $user->department_id;
        }

        // Employees can raise consultation requests on their own department's requirements
        if ($user->isEmployee()) {
            return $requirement !== null && $requirement->department_id === $user->department_id;
        }

        return false;
    }

    /**
     * Determine whether the authenticated user can close or edit a deliberation.
     */
    private function canManage(Deliberation $deliberation): bool
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isHod()) {
            $requirement = $deliberation->requirement;

            return $requirement === null || $requirement->department_id === $user->department_id;
        }

        return false;
    }

    /**
     * Determine whether the authenticated user can delete a deliberation.
     */
    private function canDelete(Deliberation $deliberation): bool
    {
        $user = Auth::user();

        return $user->isAdmin() || $deliberation->comments()->count() === 0;
    }

    /**
     * Determine whether the authenticated user can escalate a deliberation.
     */
    private function canEscalate(): bool
    {
        $user = Auth::user();

        return $user->isAdmin() || $user->isDirector() || $user->isHod();
    }

    /**
     * Load selectable participants for create/edit forms.
     */
    private function selectableParticipants(): Collection
    {
        return User::where('is_approved', true)
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function index(Event $event): View
    {
        $event->load([
            'deliberations.requirement',
            'deliberations.comments.user',
            'deliberations.approvedBy',
            'deliberations.escalatedTo',
            'deliberations.participants',
            'deliberations.attachments',
        ]);

        $deliberations = $event->deliberations()
            ->with(['requirement', 'comments.user', 'approvedBy', 'escalatedTo', 'participants', 'attachments'])
            ->orderByRaw("CASE impact_level WHEN 'critical' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('decision_due_date')
            ->get();

        $openCount = $deliberations->where('status', DeliberationStatus::Open)->count();
        $overdueCount = $deliberations->filter(fn ($d) => $d->isOverdue())->count();
        $criticalCount = $deliberations->where('impact_level', 'critical')->where('status', '!=', DeliberationStatus::Closed)->count();
        $escalatedCount = $deliberations->where('status', DeliberationStatus::Escalated)->count();

        return view('deliberations.index', compact('event', 'deliberations', 'openCount', 'overdueCount', 'criticalCount', 'escalatedCount'));
    }

    public function create(Event $event, Request $request): View
    {
        $user = Auth::user();
        $selectedRequirementId = $request->integer('requirement_id') ?: null;
        $isConsultation = $request->boolean('consult') || $user->isEmployee();

        // Employees can only raise consultations on their own department's requirements
        if ($user->isEmployee()) {
            abort_unless($selectedRequirementId, 403, 'Please select a requirement to consult on.');
            $req = Requirement::findOrFail($selectedRequirementId);
            abort_unless($req->department_id === $user->department_id, 403, 'You can only consult on your own department\'s requirements.');
        } else {
            abort_unless($this->canCreate(), 403, 'You do not have permission to create deliberations.');
        }

        $participants = $this->selectableParticipants();
        $requirements = $event->requirements()
            ->when($user->isHod() || $user->isEmployee(), fn ($q) => $q->where('department_id', $user->department_id))
            ->orderBy('description')
            ->get();

        return view('deliberations.create', compact(
            'event', 'participants', 'requirements', 'selectedRequirementId', 'isConsultation'
        ));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'type' => ['nullable', 'in:deliberation,consultation'],
            'requirement_id' => ['nullable', 'exists:requirements,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'decision_due_date' => ['nullable', 'date'],
            'impact_level' => ['required', 'in:critical,medium,low'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['exists:users,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $type = $validated['type'] ?? ($user->isEmployee() ? 'consultation' : 'deliberation');
        $requirement = null;

        if ($validated['requirement_id'] ?? null) {
            $requirement = Requirement::findOrFail($validated['requirement_id']);
            abort_unless($requirement->event_id === $event->id, 403);
            abort_unless($this->canCreate($requirement), 403, 'You can only create deliberations for your own department.');

            // Employees raise consultations; only HOD/admin can put a requirement under full deliberation
            if ($user->isEmployee()) {
                $type = 'consultation';
            } else {
                $requirement->setStatus(RequirementStatus::UnderDeliberation);
            }
        } else {
            abort_unless($this->canCreate(), 403, 'You do not have permission to create event-level deliberations.');
        }

        $deliberation = Deliberation::create([
            'event_id' => $event->id,
            'requirement_id' => $validated['requirement_id'] ?? null,
            'type' => $type,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'recommendation' => $validated['recommendation'] ?? null,
            'status' => DeliberationStatus::Open,
            'decision_due_date' => $validated['decision_due_date'] ?? null,
            'impact_level' => $validated['impact_level'],
            'created_by' => Auth::id(),
        ]);

        $this->syncParticipants($deliberation, $validated['participants'] ?? []);
        $this->storeAttachments($deliberation, $request->file('attachments', []));

        // For consultation — auto-add the department's HOD as participant so they get notified
        if ($type === 'consultation' && $requirement) {
            $hod = User::where('role', 'hod')
                ->where('department_id', $requirement->department_id)
                ->where('is_approved', true)
                ->first();
            if ($hod && ! $deliberation->hasParticipant($hod)) {
                $deliberation->participants()->attach($hod->id);
            }
        }

        $this->notifyOnCreate($deliberation, $requirement);
        $this->notifyDirectedParticipants($deliberation);

        return redirect()->route('deliberations.show', [$event, $deliberation])
            ->with('success', $type === 'consultation'
                ? 'Consultation request raised. Your HOD has been notified.'
                : 'Deliberation created.');
    }

    public function show(Event $event, Deliberation $deliberation): View
    {
        abort_unless($deliberation->event_id === $event->id, 404);

        $deliberation->load(['requirement', 'comments.user', 'approvedBy', 'escalatedTo', 'event', 'participants', 'attachments.user']);

        $canManage = $this->canManage($deliberation);
        $canDelete = $this->canDelete($deliberation);
        $canEscalate = $this->canEscalate() && $deliberation->canBeEscalated();
        $isParticipant = $deliberation->hasParticipant(Auth::user());
        $participantResponse = $deliberation->participantResponseFor(Auth::user());
        $approvalSummary = $deliberation->approvalSummary();
        $admins = User::where('role', UserRole::Admin)->where('is_approved', true)->get();

        return view('deliberations.show', compact(
            'event',
            'deliberation',
            'canManage',
            'canDelete',
            'canEscalate',
            'isParticipant',
            'participantResponse',
            'approvalSummary',
            'admins'
        ));
    }

    public function edit(Event $event, Deliberation $deliberation): View
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless($deliberation->status->isOpen(), 403, 'Only open deliberations can be edited.');
        abort_unless($this->canManage($deliberation), 403);

        $participants = $this->selectableParticipants();
        $requirements = $event->requirements()
            ->when(Auth::user()->isHod(), fn ($q) => $q->where('department_id', Auth::user()->department_id))
            ->orderBy('description')
            ->get();
        $selectedParticipants = $deliberation->participants->pluck('id')->all();

        return view('deliberations.edit', compact('event', 'deliberation', 'participants', 'requirements', 'selectedParticipants'));
    }

    public function update(Request $request, Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless($deliberation->status->isOpen(), 403, 'Only open deliberations can be edited.');
        abort_unless($this->canManage($deliberation), 403);

        $validated = $request->validate([
            'requirement_id' => ['nullable', 'exists:requirements,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'decision_due_date' => ['nullable', 'date'],
            'impact_level' => ['required', 'in:critical,medium,low'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['exists:users,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $requirement = null;
        if ($validated['requirement_id'] ?? null) {
            $requirement = Requirement::findOrFail($validated['requirement_id']);
            abort_unless($requirement->event_id === $event->id, 403);

            if (Auth::user()->isHod()) {
                abort_unless($requirement->department_id === Auth::user()->department_id, 403);
            }
        }

        $deliberation->update([
            'requirement_id' => $validated['requirement_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'recommendation' => $validated['recommendation'] ?? null,
            'decision_due_date' => $validated['decision_due_date'] ?? null,
            'impact_level' => $validated['impact_level'],
        ]);

        $this->syncParticipants($deliberation, $validated['participants'] ?? []);
        $this->storeAttachments($deliberation, $request->file('attachments', []));

        $this->notifyDirectedParticipants($deliberation);

        return redirect()->route('deliberations.show', [$event, $deliberation])
            ->with('success', 'Deliberation updated.');
    }

    public function destroy(Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless($this->canDelete($deliberation), 403);

        $deliberation->comments()->delete();
        $deliberation->attachments()->delete();
        $deliberation->delete();

        return redirect()->route('deliberations.index', $event)
            ->with('success', 'Deliberation deleted.');
    }

    public function addComment(Request $request, Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless(! $deliberation->status->isClosed(), 403, 'This deliberation is closed.');

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $comment = DeliberationComment::create([
            'deliberation_id' => $deliberation->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        $this->notifyOnComment($deliberation, $comment);

        return back()->with('success', 'Comment added.');
    }

    public function close(Request $request, Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless(! $deliberation->status->isClosed(), 403, 'This deliberation is already closed.');
        abort_unless($this->canManage($deliberation), 403);

        $validated = $request->validate([
            'outcome' => ['required', Rule::enum(DecisionOutcome::class)],
            'decision_notes' => ['required', 'string', 'max:2000'],
        ]);

        $outcome = DecisionOutcome::from($validated['outcome']);

        $deliberation->update([
            'status' => DeliberationStatus::Closed,
            'outcome' => $outcome,
            'decision_made' => true,
            'decision_notes' => $validated['decision_notes'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        if ($deliberation->requirement) {
            $deliberation->requirement->setStatus($outcome->requirementStatus());
        }

        $this->notifyOnClose($deliberation);

        return back()->with('success', 'Deliberation closed with decision: '.$outcome->label().'.');
    }

    public function escalate(Request $request, Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless($this->canEscalate(), 403);
        abort_unless($deliberation->canBeEscalated(), 403, 'Closed deliberations cannot be escalated.');

        $validated = $request->validate([
            'escalated_to' => ['required', 'exists:users,id'],
        ]);

        $escalatedTo = User::findOrFail($validated['escalated_to']);
        $deliberation->markEscalated($escalatedTo);

        $escalatedTo->notify(new DeliberationEscalatedNotification($deliberation, Auth::user()));

        return back()->with('success', 'Deliberation escalated to '.$escalatedTo->name.'.');
    }

    /**
     * Sync deliberation participants, preserving existing approval responses.
     */
    private function syncParticipants(Deliberation $deliberation, array $participantIds): void
    {
        $participantIds = array_unique(array_map('intval', array_filter($participantIds)));

        $existing = $deliberation->participants()
            ->whereIn('user_id', $participantIds)
            ->get()
            ->keyBy('id');

        $syncData = [];
        foreach ($participantIds as $userId) {
            $syncData[$userId] = [
                'approved_at' => $existing->get($userId)?->pivot?->approved_at,
                'rejected_at' => $existing->get($userId)?->pivot?->rejected_at,
                'notes' => $existing->get($userId)?->pivot?->notes,
            ];
        }

        $deliberation->participants()->sync($syncData);
    }

    /**
     * Store uploaded attachments for a deliberation.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function storeAttachments(Deliberation $deliberation, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('deliberations/'.$deliberation->id, 'local');

            DeliberationAttachment::create([
                'deliberation_id' => $deliberation->id,
                'user_id' => Auth::id(),
                'filename' => $file->hashName(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Notify relevant users when a deliberation is created.
     */
    private function notifyOnCreate(Deliberation $deliberation, ?Requirement $requirement): void
    {
        if ($requirement === null) {
            return;
        }

        $recipients = User::where('department_id', $requirement->department_id)
            ->whereIn('role', [UserRole::Hod, UserRole::Employee])
            ->where('id', '!=', Auth::id())
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new DeliberationCreatedNotification($deliberation, Auth::user()));
        }
    }

    /**
     * Notify newly added directed participants.
     */
    private function notifyDirectedParticipants(Deliberation $deliberation): void
    {
        foreach ($deliberation->participants as $participant) {
            if ($participant->id === Auth::id()) {
                continue;
            }

            $participant->notify(new DeliberationDirectedNotification($deliberation, Auth::user()));
        }
    }

    /**
     * Notify participants when a comment is added.
     */
    private function notifyOnComment(Deliberation $deliberation, DeliberationComment $comment): void
    {
        $participantIds = $deliberation->participants->pluck('id');
        $commenterIds = $deliberation->comments()
            ->where('user_id', '!=', Auth::id())
            ->distinct()
            ->pluck('user_id');

        $notifyIds = $participantIds->merge($commenterIds)
            ->push($deliberation->approved_by)
            ->filter()
            ->unique()
            ->reject(fn ($id) => $id === Auth::id());

        User::whereIn('id', $notifyIds)
            ->get()
            ->each(fn (User $user) => $user->notify(new DeliberationCommentedNotification($deliberation, $comment, Auth::user())));
    }

    /**
     * Notify participants when a deliberation is closed.
     */
    private function notifyOnClose(Deliberation $deliberation): void
    {
        $notifyIds = $deliberation->participants->pluck('id')
            ->merge($deliberation->comments()->pluck('user_id'))
            ->push($deliberation->requirement?->assigned_to)
            ->filter()
            ->unique()
            ->reject(fn ($id) => $id === Auth::id());

        User::whereIn('id', $notifyIds)
            ->get()
            ->each(fn (User $user) => $user->notify(new DeliberationClosedNotification($deliberation, Auth::user())));
    }
}
