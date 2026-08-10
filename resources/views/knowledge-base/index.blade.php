<x-layouts.app>
    <x-slot:title>Knowledge Base</x-slot:title>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Knowledge Base</h1>
            <p class="text-sm text-gray-500 mt-1">Lessons learned, best practices and recommendations from past events</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="inline-flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                {{ $totalReviews }} post-event reviews
            </span>
            @if ($recentReviews > 0)
                <span class="inline-flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                    {{ $recentReviews }} in last 3 months
                </span>
            @endif
        </div>
    </div>

    {{-- Search + filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <div class="flex-1 min-w-48 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Search lessons, challenges, recommendations…"
                class="block w-full rounded-xl border border-gray-300 pl-9 pr-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
        </div>
        <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
            @foreach (['' => 'All', 'lessons' => 'Lessons', 'challenges' => 'Challenges', 'recommendations' => 'Recommendations'] as $val => $label)
                <a href="{{ route('knowledge-base.index', array_filter(['search' => $search, 'category' => $val])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                        {{ $category === $val ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        @if ($search)
            <a href="{{ route('knowledge-base.index') }}" class="self-center text-sm text-gray-400 hover:text-gray-600">Clear</a>
        @endif
    </form>

    {{-- Recurring issues callout --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6 flex flex-col sm:flex-row sm:items-start gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-lg">🔁</span>
                <h2 class="font-semibold text-gray-900 text-sm">Recurring Issues Tracker</h2>
                @if ($criticalRecurring > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                        {{ $criticalRecurring }} critical open
                    </span>
                @endif
            </div>
            @if ($openRecurring->isEmpty())
                <p class="text-sm text-gray-400">No recurring issues tracked yet.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($openRecurring as $ri)
                        @php $sc = $ri->severityClasses(); @endphp
                        <a href="{{ route('recurring-issues.show', $ri) }}"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }} hover:opacity-80 transition-opacity">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                            {{ Str::limit($ri->title, 40) }}
                            <span class="font-bold opacity-60">{{ $ri->occurrences_count }}×</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('recurring-issues.index') }}"
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                View all ({{ $totalRecurring }})
            </a>
            <a href="{{ route('recurring-issues.create') }}"
                class="text-sm text-white bg-indigo-600 hover:bg-indigo-700 font-medium px-3 py-1.5 rounded-lg transition-colors">
                + Log Issue
            </a>
        </div>
    </div>

    {{-- Results --}}
    @if ($reviews->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">            <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-500 font-medium">No post-event reviews found</p>
            <p class="text-sm text-gray-400 mt-1">
                {{ $search ? 'Try a different search term' : 'Reviews will appear here after events are completed' }}
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
            @foreach ($reviews as $review)
                <a href="{{ route('knowledge-base.show', $review) }}"
                    class="group bg-white rounded-2xl border border-gray-200 hover:border-indigo-300 shadow-sm hover:shadow-md p-5 transition-all flex flex-col gap-3">

                    {{-- Event meta --}}
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-2">
                            {{ $review->event->type ?? 'Event' }}
                        </span>
                        <h3 class="font-semibold text-gray-900 text-sm group-hover:text-indigo-700 transition-colors leading-snug">
                            {{ $review->event->name }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $review->event->event_date->format('d M Y') }}
                            @if ($review->completedBy) · Reviewed by {{ $review->completedBy->name }} @endif
                        </p>
                    </div>

                    {{-- Preview snippets --}}
                    <div class="space-y-2 flex-1">
                        @if ($review->what_went_well)
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 text-green-500 shrink-0">✓</span>
                                <p class="text-xs text-gray-600 leading-snug line-clamp-2">
                                    {{ Str::limit($review->what_went_well, 100) }}
                                </p>
                            </div>
                        @endif
                        @if ($review->lessons_learned)
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 text-indigo-500 shrink-0">💡</span>
                                <p class="text-xs text-gray-600 leading-snug line-clamp-2">
                                    {{ Str::limit($review->lessons_learned, 100) }}
                                </p>
                            </div>
                        @endif
                        @if ($review->challenges)
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 text-amber-500 shrink-0">⚠</span>
                                <p class="text-xs text-gray-600 leading-snug line-clamp-2">
                                    {{ Str::limit($review->challenges, 100) }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-xs text-gray-400">
                            {{ $review->completed_at?->diffForHumans() ?? 'No date' }}
                        </span>
                        <span class="text-xs text-indigo-600 font-medium group-hover:underline">Read more →</span>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $reviews->links() }}
    @endif
</x-layouts.app>
