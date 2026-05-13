<x-layouts.admin>
    <x-slot:title>Events</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Manage Events</h2>
            <p class="text-sm text-gray-500">Add events and set department requirements</p>
        </div>
        <a href="{{ route('admin.events.create') }}"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Event
        </a>
    </div>

    {{-- Week selector --}}
    <form method="GET" id="week-form" class="mb-5">
        <select name="week" onchange="document.getElementById('week-form').submit()"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            @foreach ($weeks as $week)
                <option value="{{ $week->id }}" @selected($week->id === $selectedWeek?->id)>
                    {{ $week->label }}{{ $week->is_current ? ' (Current)' : '' }} — {{ $week->events_count }} event(s)
                </option>
            @endforeach
        </select>
    </form>

    @if ($events->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 text-center py-16">
            <p class="text-gray-400 text-sm">No events for this week.</p>
            <a href="{{ route('admin.events.create') }}" class="mt-3 inline-block text-indigo-600 text-sm font-medium hover:text-indigo-700">Add one →</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($events as $event)
                @php $overall = $event->overallReadiness(); $dc = \App\Models\Department::ragClasses($overall); @endphp
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-gray-900">{{ $event->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ $event->type }}</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $dc['bg'] }} {{ $dc['text'] }}">
                                {{ $overall }}% ready
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $event->event_date->format('D, d M Y') }}
                            @if ($event->event_time) &bull; {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }} @endif
                            @if ($event->venue) &bull; {{ $event->venue }} @endif
                        </p>
                        <div class="mt-2 bg-gray-200 rounded-full h-1.5 overflow-hidden max-w-xs">
                            <div class="{{ $dc['bar'] }} h-1.5 rounded-full" style="width: {{ $overall }}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.events.show', $event) }}"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Requirements</a>
                        <a href="{{ route('admin.events.edit', $event) }}"
                            class="text-sm text-gray-500 hover:text-gray-700 font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                            onsubmit="return confirm('Delete {{ $event->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
