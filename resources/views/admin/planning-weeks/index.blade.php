<x-layouts.admin>
    <x-slot:title>Planning Weeks</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Planning Weeks</h2>
            <p class="text-sm text-gray-500">Manage weekly schedules and import KSG programme calendars</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.planning-weeks.import') }}"
                class="inline-flex items-center gap-1.5 border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Import schedule
            </a>
            <a href="{{ route('admin.planning-weeks.create') }}"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                New week
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    @if ($weeks->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 text-center py-16 px-6">
            <p class="text-gray-600 font-medium">No planning weeks yet</p>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                Import the weekly schedule JSON (KSG format) or create a week manually, then add events with readiness requirements.
            </p>
            <a href="{{ route('admin.planning-weeks.import') }}" class="mt-4 inline-block text-indigo-600 text-sm font-medium hover:text-indigo-700">
                Import 18–22 May 2026 schedule →
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3">Week</th>
                        <th class="px-5 py-3">Campus</th>
                        <th class="px-5 py-3">Events</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($weeks as $week)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.planning-weeks.show', $week) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $week->label }}
                                </a>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $week->week_start->format('d M Y') }} – {{ $week->week_end->format('d M Y') }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $week->campus ?? '—' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $week->events_count }}</td>
                            <td class="px-5 py-4">
                                @if ($week->is_current)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Current</span>
                                @else
                                    <form method="POST" action="{{ route('admin.planning-weeks.set-current', $week) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Set as current</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-3">
                                <a href="{{ route('admin.events.create', ['planning_week' => $week->id]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Add event</a>
                                <a href="{{ route('admin.planning-weeks.edit', $week) }}" class="text-gray-500 hover:text-gray-700">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
</div>
        </div>
    @endif
</x-layouts.admin>
