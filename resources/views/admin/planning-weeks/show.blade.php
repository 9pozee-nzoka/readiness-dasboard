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
            <a href="{{ route('admin.accommodation.edit', $week) }}"
                class="inline-flex items-center gap-1.5 border border-orange-300 bg-orange-50 text-orange-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-orange-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Accommodation
            </a>
            <a href="{{ route('reports.weekly-schedule', ['week' => $week->id]) }}" target="_blank"
                class="inline-flex items-center gap-1.5 border border-indigo-300 bg-indigo-50 text-indigo-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-indigo-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                KSG Schedule
            </a>
            <button type="button" onclick="document.getElementById('share-modal').showModal()"
                class="inline-flex items-center gap-1.5 border border-green-300 bg-green-50 text-green-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Share via Email
            </button>
        </div>
    </div>

    {{-- Share Modal --}}
    <dialog id="share-modal" class="modal p-6 rounded-xl shadow-xl border border-gray-200 w-full max-w-md">
        <form method="POST" action="{{ route('admin.planning-weeks.share', $week) }}">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Share Weekly Schedule</h3>
                <button type="button" onclick="document.getElementById('share-modal').close()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Select departments to share this week's schedule with:</p>
            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-2 mb-4">
                @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $department)
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 p-1 rounded">
                        <input type="checkbox" name="departments[]" value="{{ $department->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>{{ $department->name }}</span>
                    </label>
                @endforeach
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('share-modal').close()" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Cancel</button>
                <button type="submit" name="share_type" value="selected" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Share to Selected</button>
                <a href="{{ route('admin.planning-weeks.share-all', $week) }}" onclick="event.preventDefault(); document.getElementById('share-all-form').submit();" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">Share to All</a>
            </div>
            <form id="share-all-form" method="POST" action="{{ route('admin.planning-weeks.share-all', $week) }}" class="hidden">
                @csrf
            </form>
        </form>
    </dialog>

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
