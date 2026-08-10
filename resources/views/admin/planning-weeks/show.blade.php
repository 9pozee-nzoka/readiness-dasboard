<x-layouts.admin>
    <x-slot:title>{{ $week->label }}</x-slot:title>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $week->label }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $week->week_start->format('d M Y') }} – {{ $week->week_end->format('d M Y') }}
                @if ($week->campus) &bull; {{ $week->campus }} campus @endif
                @if ($week->schedule_reference) &bull; {{ $week->schedule_reference }} @endif
                @if ($week->is_current) &bull; <span class="text-indigo-600 font-medium">Current week</span> @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events.create', ['planning_week' => $week->id]) }}"
                class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                Add event with requirements
            </a>
            <a href="{{ route('admin.planning-weeks.edit', $week) }}"
                class="inline-flex items-center border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg hover:border-gray-400">
                Edit week
            </a>
        </div>
    </div>

    @php
        $grouped = $week->events->groupBy(fn ($e) => $e->schedule_category?->label() ?? 'Uncategorised');
    @endphp

    @if ($week->events->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 text-center py-12">
            <p class="text-gray-500 text-sm">No programmes for this week yet.</p>
            <a href="{{ route('admin.planning-weeks.import') }}" class="mt-2 inline-block text-indigo-600 text-sm font-medium">Import weekly schedule →</a>
        </div>
    @else
        @foreach ($grouped as $section => $events)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">{{ $section }}</h3>
                <div class="space-y-2">
                    @foreach ($events as $event)
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('admin.events.show', $event) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $event->name }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $event->dateRangeLabel() }}
                                    @if ($event->client) &bull; {{ $event->client }} @endif
                                    @if ($event->venue) &bull; {{ $event->venue }} @endif
                                    @if ($event->totalParticipants()) &bull; {{ $event->totalParticipants() }} participants @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ $event->type }}</span>
                                <span class="text-xs text-gray-500">{{ $event->requirements_count }} reqs</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</x-layouts.admin>
