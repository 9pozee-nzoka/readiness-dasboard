<x-layouts.app>
    <x-slot:title>{{ $review->event->name }} — Review</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('knowledge-base.index') }}" class="hover:text-indigo-600">Knowledge Base</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium truncate">{{ $review->event->name }}</span>
    </nav>

    <div class="max-w-3xl w-full">
        {{-- Header card --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-3">
                {{ $review->event->type ?? 'Event' }}
            </span>
            <h1 class="text-xl font-bold text-gray-900">{{ $review->event->name }}</h1>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-gray-500">
                <span>📅 {{ $review->event->event_date->format('d F Y') }}</span>
                @if ($review->event->venue) <span>📍 {{ $review->event->venue }}</span> @endif
                @if ($review->completedBy) <span>Reviewed by {{ $review->completedBy->name }}</span> @endif
                @if ($review->completed_at) <span>{{ $review->completed_at->format('d M Y') }}</span> @endif
            </div>
        </div>

        <div class="space-y-5">
            @foreach ([
                ['icon' => '✅', 'color' => 'green',  'title' => 'What Went Well',     'content' => $review->what_went_well],
                ['icon' => '⚠️', 'color' => 'amber',  'title' => 'Challenges Faced',   'content' => $review->challenges],
                ['icon' => '💡', 'color' => 'indigo', 'title' => 'Lessons Learned',    'content' => $review->lessons_learned],
                ['icon' => '📋', 'color' => 'blue',   'title' => 'Recommendations',    'content' => $review->recommendations],
            ] as $section)
                @if ($section['content'])
                    @php
                        $colors = [
                            'green'  => ['bg' => 'bg-green-50',  'border' => 'border-green-200', 'title' => 'text-green-800',  'icon_bg' => 'bg-green-100'],
                            'amber'  => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200', 'title' => 'text-amber-800',  'icon_bg' => 'bg-amber-100'],
                            'indigo' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200','title' => 'text-indigo-800', 'icon_bg' => 'bg-indigo-100'],
                            'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',  'title' => 'text-blue-800',   'icon_bg' => 'bg-blue-100'],
                        ];
                        $c = $colors[$section['color']];
                    @endphp
                    <div class="{{ $c['bg'] }} {{ $c['border'] }} border rounded-2xl p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg {{ $c['icon_bg'] }} flex items-center justify-center text-base">
                                {{ $section['icon'] }}
                            </div>
                            <h2 class="font-semibold {{ $c['title'] }} text-sm">{{ $section['title'] }}</h2>
                        </div>
                        <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $section['content'] }}</div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200 flex items-center gap-3">
            <a href="{{ route('knowledge-base.index') }}"
                class="text-sm text-gray-500 hover:text-gray-700 font-medium">← Back to Knowledge Base</a>
            <a href="{{ route('events.show', $review->event) }}"
                class="ml-auto text-sm text-indigo-600 hover:text-indigo-700 font-medium">View Event →</a>
        </div>
    </div>
</x-layouts.app>
