<x-layouts.app>
    <x-slot:title>{{ $event->name }} — Deliberations</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('dashboard.index') }}" class="hover:text-indigo-600">Dashboard</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('events.show', $event) }}" class="hover:text-indigo-600 truncate">{{ $event->name }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 font-medium">Deliberations</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Deliberations</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $event->name }} — {{ $event->event_date->format('d M Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @php $user = auth()->user(); @endphp
            @if ($user->isAdmin() || $user->isHod())
                <a href="{{ route('deliberations.create', $event) }}"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Deliberation
                </a>
            @endif
            <a href="{{ route('events.show', $event) }}"
                class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                ← Back to Event
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Open</p>
            <p class="text-2xl font-bold text-indigo-700 mt-1">{{ $openCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Overdue</p>
            <p class="text-2xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $overdueCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Critical</p>
            <p class="text-2xl font-bold {{ $criticalCount > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $criticalCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Escalated</p>
            <p class="text-2xl font-bold {{ $escalatedCount > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $escalatedCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-medium text-gray-500 uppercase">Closed</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ $deliberations->where('status', \App\Enums\DeliberationStatus::Closed)->count() }}</p>
        </div>
    </div>

    {{-- Deliberations List --}}
    <div class="space-y-4">
        @foreach ($deliberations as $deliberation)
            @php
                $isOverdue = $deliberation->isOverdue();
                $isCritical = $deliberation->isCritical();
                $status = $deliberation->status;
                $statusClasses = match($status) {
                    \App\Enums\DeliberationStatus::Open => $isOverdue ? 'bg-red-50 border-red-200' : ($isCritical ? 'bg-purple-50 border-purple-200' : 'bg-white border-gray-200'),
                    \App\Enums\DeliberationStatus::Escalated => 'bg-red-50 border-red-200',
                    \App\Enums\DeliberationStatus::Closed => 'bg-green-50 border-green-200',
                };
                $summary = $deliberation->approvalSummary();
            @endphp
            <div class="rounded-xl border shadow-sm overflow-hidden {{ $statusClasses }}">
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h3 class="font-semibold text-gray-900">{{ $deliberation->title }}</h3>
                                {{-- Type badge --}}
                                @if ($deliberation->isConsultation())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                        🙋 Consultation
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ match($deliberation->impact_level) {
                                        'critical' => 'bg-red-100 text-red-700',
                                        'medium' => 'bg-amber-100 text-amber-700',
                                        'low' => 'bg-gray-100 text-gray-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ ucfirst($deliberation->impact_level) }}
                                </span>
                                @php $sc = $status->classes(); @endphp
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
                                <p class="text-sm text-gray-600 mt-1">{{ $deliberation->description }}</p>
                            @endif
                            @if ($deliberation->requirement)
                                <p class="text-xs text-gray-500 mt-1">
                                    Linked requirement: <span class="font-medium">{{ $deliberation->requirement->description }}</span>
                                    @if ($deliberation->requirement->department)
                                        <span class="text-gray-400">({{ $deliberation->requirement->department->name }})</span>
                                    @endif
                                </p>
                            @endif
                            @if ($deliberation->escalatedTo)
                                <p class="text-xs text-red-600 mt-1 font-medium">
                                    Escalated to {{ $deliberation->escalatedTo->name }} on {{ $deliberation->escalated_at?->format('d M Y') }}
                                </p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-400">
                                @if ($deliberation->decision_due_date)
                                    <span>Decision due: {{ $deliberation->decision_due_date->format('d M Y') }}</span>
                                @endif
                                <span>{{ $deliberation->comments->count() }} comment{{ $deliberation->comments->count() !== 1 ? 's' : '' }}</span>
                                @if ($deliberation->attachments->isNotEmpty())
                                    <span>{{ $deliberation->attachments->count() }} attachment{{ $deliberation->attachments->count() !== 1 ? 's' : '' }}</span>
                                @endif
                                @if ($deliberation->participants->isNotEmpty())
                                    <span>{{ $summary['approved'] }}/{{ $summary['total'] }} approvals</span>
                                @endif
                                <span>Created {{ $deliberation->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <a href="{{ route('deliberations.show', [$event, $deliberation]) }}"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium shrink-0">
                            View Discussion →
                        </a>
                    </div>

                    @if ($deliberation->status->isClosed() && $deliberation->decision_made)
                        <div class="mt-3 pt-3 border-t border-gray-200/60">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Decision Record</p>
                            <p class="text-sm text-gray-700">{{ $deliberation->decision_notes }}</p>
                            @if ($deliberation->approvedBy)
                                <p class="text-xs text-gray-400 mt-1">
                                    Closed by {{ $deliberation->approvedBy->name }} on {{ $deliberation->approved_at?->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($deliberations->isEmpty())
            <div class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                <p class="text-gray-500 font-medium">No deliberations yet</p>
                <p class="text-sm text-gray-400 mt-1">Create a deliberation for requirements needing discussion.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
