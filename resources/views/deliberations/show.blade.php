<x-layouts.app>
    <x-slot:title>{{ $deliberation->title }} — Deliberation</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('dashboard.index') }}" class="hover:text-indigo-600">Dashboard</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('events.show', $event) }}" class="hover:text-indigo-600 truncate">{{ $event->name }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('deliberations.index', $event) }}" class="hover:text-indigo-600">Deliberations</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 font-medium truncate">{{ $deliberation->title }}</span>
    </nav>

    @php
        $isOverdue = $deliberation->isOverdue();
        $isCritical = $deliberation->isCritical();
        $status = $deliberation->status;
        $sc = $status->classes();
    @endphp

    {{-- Header --}}
    <div class="bg-white rounded-xl border {{ $isOverdue || $status->isEscalated() ? 'border-red-300' : ($isCritical ? 'border-purple-300' : 'border-gray-200') }} shadow-sm p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    <h1 class="text-xl font-bold text-gray-900">{{ $deliberation->title }}</h1>
                    {{-- Type badge --}}
                    @if ($deliberation->isConsultation())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                            🙋 Consultation Request
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                            💬 Deliberation
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                        {{ match($deliberation->impact_level) {
                            'critical' => 'bg-red-100 text-red-700',
                            'medium' => 'bg-amber-100 text-amber-700',
                            'low' => 'bg-gray-100 text-gray-600',
                            default => 'bg-gray-100 text-gray-600',
                        } }}">
                        {{ ucfirst($deliberation->impact_level) }} Impact
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sc['bg'] }} {{ $sc['text'] }} border {{ $sc['border'] }}">
                        {{ $status->label() }}
                    </span>
                    @if ($isOverdue)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">
                            ⚠ Overdue
                        </span>
                    @endif
                    @if ($deliberation->outcome)
                        @php $oc = $deliberation->outcome->classes(); @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $oc['bg'] }} {{ $oc['text'] }} border {{ $oc['border'] }}">
                            {{ $deliberation->outcome->label() }}
                        </span>
                    @endif
                </div>
                @if ($deliberation->description)
                    <p class="text-sm text-gray-600">{{ $deliberation->description }}</p>
                @endif
                @if ($deliberation->requirement)
                    <p class="text-xs text-gray-500 mt-2">
                        Linked requirement: <a href="{{ route('events.show', $event) }}#req-{{ $deliberation->requirement_id }}" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ $deliberation->requirement->description }}</a>
                        @if ($deliberation->requirement->department)
                            <span class="text-gray-400">({{ $deliberation->requirement->department->name }})</span>
                        @endif
                    </p>
                @endif
                @if ($deliberation->escalatedTo)
                    <p class="text-xs text-red-600 mt-2 font-medium">
                        Escalated to {{ $deliberation->escalatedTo->name }} on {{ $deliberation->escalated_at?->format('d M Y H:i') }}
                    </p>
                @endif
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 text-xs text-gray-400">
                    @if ($deliberation->decision_due_date)
                        <span class="{{ $isOverdue ? 'text-red-600 font-semibold' : '' }}">
                            Decision due: {{ $deliberation->decision_due_date->format('d M Y') }}
                        </span>
                    @endif
                    <span>Created {{ $deliberation->created_at->format('d M Y H:i') }}</span>
                    @if ($deliberation->createdBy)
                        <span>By {{ $deliberation->createdBy->name }}</span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($canManage && $deliberation->status->isOpen())
                    <a href="{{ route('deliberations.edit', [$event, $deliberation]) }}"
                        class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        Edit
                    </a>
                @endif
                @if ($canDelete)
                    <form method="POST" action="{{ route('deliberations.destroy', [$event, $deliberation]) }}"
                        onsubmit="return confirm('Delete this deliberation and all its comments?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Delete</button>
                    </form>
                @endif
                <a href="{{ route('deliberations.index', $event) }}"
                    class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                    ← All Deliberations
                </a>
            </div>
        </div>

        @if ($deliberation->status->isClosed() && $deliberation->decision_made)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Decision Record</p>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-gray-800">{{ $deliberation->decision_notes }}</p>
                    @if ($deliberation->approvedBy)
                        <p class="text-xs text-gray-500 mt-2">
                            Closed by <span class="font-medium">{{ $deliberation->approvedBy->name }}</span>
                            on {{ $deliberation->approved_at?->format('d M Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Recommendation --}}
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-6 mb-6">
        <p class="text-xs font-medium text-indigo-700 uppercase mb-2">Recommendation</p>
        @if ($deliberation->recommendation)
            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $deliberation->recommendation }}</p>
        @else
            <p class="text-sm text-gray-500 italic">No recommendation provided.</p>
        @endif
    </div>

    {{-- Attachments --}}
    @if ($deliberation->attachments->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4">Evidence / Attachments ({{ $deliberation->attachments->count() }})</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($deliberation->attachments as $attachment)
                    <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                            @if ($attachment->isImage())
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @elseif ($attachment->isPdf())
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                            <p class="text-xs text-gray-500">{{ $attachment->formattedSize() }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <a href="{{ $attachment->downloadUrl() }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Download</a>
                                @if ($attachment->canBePreviewed())
                                    <a href="{{ $attachment->previewUrl() }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Preview</a>
                                @endif
                                @if (auth()->user()->isAdmin() || $attachment->user_id === auth()->id())
                                    <form method="POST" action="{{ route('deliberations.attachments.destroy', [$event, $deliberation, $attachment]) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium" onclick="return confirm('Delete this attachment?')">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Participant Responses --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900">Participant Responses</h2>
            <div class="flex items-center gap-3 text-xs">
                <span class="text-green-600 font-medium">{{ $approvalSummary['approved'] }} approved</span>
                <span class="text-red-600 font-medium">{{ $approvalSummary['rejected'] }} rejected</span>
                <span class="text-gray-500">{{ $approvalSummary['pending'] }} pending</span>
            </div>
        </div>

        @if ($deliberation->participants->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">No participants invited.</p>
        @else
            <div class="space-y-3">
                @foreach ($deliberation->participants as $participant)
                    @php
                        $pivot = $participant->pivot;
                        $hasApproved = $pivot->approved_at !== null;
                        $hasRejected = $pivot->rejected_at !== null;
                    @endphp
                    <div class="flex items-start gap-3 p-3 rounded-lg border {{ $hasApproved ? 'border-green-200 bg-green-50' : ($hasRejected ? 'border-red-200 bg-red-50' : 'border-gray-200') }}">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs shrink-0">
                            {{ mb_strtoupper(mb_substr($participant->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium text-gray-900">{{ $participant->name }}</span>
                                <span class="text-xs text-gray-400">{{ $participant->role->label() }}</span>
                                @if ($hasApproved)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Approved</span>
                                @elseif ($hasRejected)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Pending</span>
                                @endif
                            </div>
                            @if ($pivot->notes)
                                <p class="text-sm text-gray-700 mt-1">{{ $pivot->notes }}</p>
                            @endif
                            @if ($hasApproved)
                                <p class="text-xs text-gray-500 mt-0.5">Approved {{ $pivot->approved_at->diffForHumans() }}</p>
                            @elseif ($hasRejected)
                                <p class="text-xs text-gray-500 mt-0.5">Rejected {{ $pivot->rejected_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($isParticipant && $deliberation->status->isOpen())
            <div class="mt-5 pt-5 border-t border-gray-100">
                @if ($participantResponse && $participantResponse->approved_at)
                    <p class="text-sm text-green-700 font-medium">You approved this recommendation.</p>
                @elseif ($participantResponse && $participantResponse->rejected_at)
                    <p class="text-sm text-red-700 font-medium">You rejected this recommendation.</p>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('deliberations.approve', [$event, $deliberation]) }}" class="space-y-2">
                        @csrf
                        <label class="block text-sm font-medium text-green-700">Approve recommendation</label>
                        <textarea name="notes" rows="2" placeholder="Optional approval notes..."
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-green-500"></textarea>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('deliberations.reject', [$event, $deliberation]) }}" class="space-y-2">
                        @csrf
                        <label class="block text-sm font-medium text-red-700">Reject recommendation</label>
                        <textarea name="notes" rows="2" placeholder="Optional rejection notes..."
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Reject</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- Add Attachments (for creator/admin/participants) --}}
    @if ($deliberation->status->isOpen() && (auth()->user()->isAdmin() || $isParticipant || $deliberation->created_by === auth()->id()))
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-3">Add Attachment</h2>
            <form method="POST" action="{{ route('deliberations.attachments.store', [$event, $deliberation]) }}" enctype="multipart/form-data">
                @csrf
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Upload</button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Max 10 MB per file.</p>
            </form>
        </div>
    @endif

    {{-- Escalation Form --}}
    @if ($canEscalate && $deliberation->status->isOpen())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-3">Escalate Deliberation</h2>
            <p class="text-sm text-gray-500 mb-3">Send this deliberation to an administrator or director for attention.</p>
            <form method="POST" action="{{ route('deliberations.escalate', [$event, $deliberation]) }}">
                @csrf @method('PATCH')
                <div class="flex flex-col sm:flex-row gap-3">
                    <select name="escalated_to" required
                        class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:w-64">
                        <option value="">Select recipient…</option>
                        @foreach ($admins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }} — {{ $admin->role->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Escalate
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Comments --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-4">Discussion ({{ $deliberation->comments->count() }})</h2>

        @if ($deliberation->comments->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No comments yet. Start the discussion below.</p>
        @else
            <div class="space-y-4">
                @foreach ($deliberation->comments as $comment)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs shrink-0">
                            {{ mb_strtoupper(mb_substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                <span class="text-xs text-gray-400">{{ $comment->user->role->label() }}</span>
                                <span class="text-xs text-gray-300">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1">{{ $comment->comment }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($deliberation->status->isOpen())
            <form method="POST" action="{{ route('deliberations.comments.store', [$event, $deliberation]) }}" class="mt-6 pt-4 border-t border-gray-100">
                @csrf
                <textarea name="comment" placeholder="Add your comment..." rows="3" required
                    class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                <div class="flex items-center justify-between mt-2">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Post Comment
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- Close Deliberation Form --}}
    @if ($canManage && $deliberation->status->isOpen())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Close Deliberation</h2>
            <p class="text-sm text-gray-500 mb-3">Record the final decision and outcome. The linked requirement will be updated accordingly.</p>
            <form method="POST" action="{{ route('deliberations.close', [$event, $deliberation]) }}">
                @csrf @method('PATCH')
                <div class="space-y-3">
                    <div>
                        <label for="outcome" class="block text-sm font-medium text-gray-700 mb-1">Decision outcome <span class="text-red-500">*</span></label>
                        <select name="outcome" id="outcome" required
                            class="block w-full sm:w-64 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">Select outcome…</option>
                            @foreach (\App\Enums\DecisionOutcome::cases() as $outcome)
                                <option value="{{ $outcome->value }}">{{ $outcome->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <textarea name="decision_notes" placeholder="Decision record: What was decided, by whom, and why..." rows="3" required
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Close with Decision Record
                    </button>
                </div>
            </form>
        </div>
    @endif
</x-layouts.app>
