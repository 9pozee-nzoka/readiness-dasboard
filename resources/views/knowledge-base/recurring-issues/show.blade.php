<x-layouts.app>
    <x-slot:title>{{ $recurringIssue->title }}</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('knowledge-base.index') }}" class="hover:text-indigo-600">Knowledge Base</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('recurring-issues.index') }}" class="hover:text-indigo-600">Recurring Issues</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium truncate">{{ $recurringIssue->title }}</span>
    </nav>

    @php $sc = $recurringIssue->severityClasses(); @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Left: issue detail ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                {{ ucfirst($recurringIssue->severity) }}
                            </span>
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                {{ \App\Models\RecurringIssue::categories()[$recurringIssue->category] ?? $recurringIssue->category }}
                            </span>
                            @if ($recurringIssue->isTrending())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">🔥 Trending</span>
                            @endif
                            @if ($recurringIssue->is_resolved)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">✓ Resolved</span>
                            @endif
                        </div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $recurringIssue->title }}</h1>
                        <p class="text-xs text-gray-400 mt-1">
                            Reported by {{ $recurringIssue->reportedBy?->name ?? 'Unknown' }} · {{ $recurringIssue->created_at->format('d M Y') }}
                            @if ($recurringIssue->department) · {{ $recurringIssue->department->name }} @endif
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('recurring-issues.edit', $recurringIssue) }}"
                            class="text-sm border border-gray-300 hover:border-gray-400 text-gray-700 px-3 py-1.5 rounded-lg font-medium transition-colors">Edit</a>
                        @if (! $recurringIssue->is_resolved)
                            <form method="POST" action="{{ route('recurring-issues.resolve', $recurringIssue) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-sm bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg font-medium transition-colors">
                                    Mark Resolved
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Occurrence count badge --}}
                <div class="mt-4 flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5">
                        <span class="text-3xl font-bold {{ $occurrences->count() >= 5 ? 'text-red-700' : ($occurrences->count() >= 3 ? 'text-amber-700' : 'text-gray-700') }}">
                            {{ $occurrences->count() }}
                        </span>
                        <div>
                            <p class="text-xs font-semibold text-gray-600">occurrences recorded</p>
                            @if ($recurringIssue->lastOccurredAt())
                                <p class="text-[10px] text-gray-400">Last: {{ $recurringIssue->lastOccurredAt()->format('d M Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Analysis sections --}}
            @foreach ([
                ['icon' => '📋', 'title' => 'Description',       'content' => $recurringIssue->description,     'color' => 'bg-gray-50 border-gray-200'],
                ['icon' => '🔍', 'title' => 'Root Cause',         'content' => $recurringIssue->root_cause,       'color' => 'bg-amber-50 border-amber-200'],
                ['icon' => '✅', 'title' => 'Recommended Fix',    'content' => $recurringIssue->recommended_fix,  'color' => 'bg-green-50 border-green-200'],
            ] as $sec)
                @if ($sec['content'])
                    <div class="{{ $sec['color'] }} border rounded-2xl p-5">
                        <h2 class="font-semibold text-gray-900 text-sm mb-2">{{ $sec['icon'] }} {{ $sec['title'] }}</h2>
                        <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $sec['content'] }}</p>
                    </div>
                @endif
            @endforeach

            {{-- Occurrence timeline --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h2 class="font-semibold text-gray-900 text-sm mb-4">📅 Occurrence Timeline</h2>
                @if ($occurrences->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">No occurrences logged yet.</p>
                @else
                    <ol class="relative border-l border-gray-200 ml-3 space-y-4">
                        @foreach ($occurrences as $occ)
                            <li class="ml-6">
                                <span class="absolute -left-3 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="w-2 h-2 bg-indigo-600 rounded-full"></span>
                                </span>
                                <div class="bg-gray-50 rounded-xl border border-gray-100 px-4 py-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <p class="text-xs font-semibold text-gray-700">
                                            {{ $occ->occurred_at->format('d M Y') }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            @if ($occ->event)
                                                <a href="{{ route('events.show', $occ->event) }}"
                                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                                    {{ $occ->event->name }} →
                                                </a>
                                            @endif
                                            <span class="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ $occ->source }}</span>
                                        </div>
                                    </div>
                                    @if ($occ->notes)
                                        <p class="text-xs text-gray-600 mt-1">{{ $occ->notes }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>

        {{-- ── Right: log occurrence sidebar ── --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sticky top-20">
                <h2 class="font-semibold text-gray-900 text-sm mb-4">➕ Log New Occurrence</h2>
                <form method="POST" action="{{ route('recurring-issues.occurrences.store', $recurringIssue) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Event <span class="text-gray-400">(optional)</span></label>
                        <select name="event_id"
                            class="block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">No specific event</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }} ({{ $event->event_date->format('d M Y') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                        <input type="date" name="occurred_at" value="{{ now()->format('Y-m-d') }}"
                            class="block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">What happened this time?</label>
                        <textarea name="notes" rows="3" placeholder="Brief notes on this occurrence…"
                            class="block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors">
                        Log Occurrence
                    </button>
                </form>

                {{-- Delete --}}
                @if (auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('recurring-issues.destroy', $recurringIssue) }}"
                        onsubmit="return confirm('Delete this issue and all its occurrences?')"
                        class="mt-4 pt-4 border-t border-gray-100">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete issue</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
