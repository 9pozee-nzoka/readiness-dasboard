<x-layouts.admin>
    <x-slot:title>Audit Log</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Audit Log</h2>
            <p class="text-sm text-gray-500">Track all requirement changes across events</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label for="event" class="block text-xs font-medium text-gray-500 mb-1">Event</label>
            <select name="event" id="event"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">All Events</option>
                @foreach ($events as $e)
                    <option value="{{ $e->id }}" @selected($e->id === $eventId)>{{ $e->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="user" class="block text-xs font-medium text-gray-500 mb-1">User</label>
            <select name="user" id="user"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">All Users</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected($u->id === $userId)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="action" class="block text-xs font-medium text-gray-500 mb-1">Action</label>
            <select name="action" id="action"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">All Actions</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected($a === $action)>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="date_from" class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom?->format('Y-m-d') }}"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        <div>
            <label for="date_to" class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" name="date_to" id="date_to" value="{{ $dateTo?->format('Y-m-d') }}"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Filter
            </button>
            @if ($eventId || $userId || $action || $dateFrom || $dateTo)
                <a href="{{ route('admin.audit-log.index') }}"
                    class="border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Clear
                </a>
            @endif
        </div>
    </form>

    @if ($logs->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 text-center py-16">
            <p class="text-gray-400 text-sm">No audit log entries found.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-3">Time</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Action</th>
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3">Requirement</th>
                            <th class="px-5 py-3">Changes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                    {{ $log->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-5 py-3">
                                    @if ($log->user)
                                        <span class="font-medium text-gray-900">{{ $log->user->name }}</span>
                                        <span class="text-xs text-gray-400">{{ $log->user->role->label() }}</span>
                                    @else
                                        <span class="text-gray-400">System</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ match($log->action) {
                                            'created' => 'bg-green-100 text-green-700',
                                            'completed' => 'bg-blue-100 text-blue-700',
                                            'pending' => 'bg-gray-100 text-gray-700',
                                            'escalated' => 'bg-purple-100 text-purple-700',
                                            'updated' => 'bg-amber-100 text-amber-700',
                                            'deleted' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        } }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    @if ($log->event)
                                        <a href="{{ route('admin.events.show', $log->event) }}"
                                            class="text-indigo-600 hover:text-indigo-700 font-medium">
                                            {{ Str::limit($log->event->name, 40) }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    @if ($log->requirement)
                                        {{ Str::limit($log->requirement->description, 50) }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if ($log->old_values || $log->new_values)
                                        <details class="group">
                                            <summary class="cursor-pointer text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                                View details
                                            </summary>
                                            <div class="mt-2 space-y-1 text-xs">
                                                @if ($log->old_values)
                                                    <div class="text-gray-400">
                                                        <span class="font-medium">Before:</span>
                                                        <pre class="mt-0.5 bg-gray-50 rounded p-1.5 overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                @endif
                                                @if ($log->new_values)
                                                    <div class="text-gray-600">
                                                        <span class="font-medium">After:</span>
                                                        <pre class="mt-0.5 bg-gray-50 rounded p-1.5 overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                @endif
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</x-layouts.admin>
