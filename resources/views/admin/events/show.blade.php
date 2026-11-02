<x-layouts.admin>
    <x-slot:title>{{ $event->name }} — Requirements</x-slot:title>

    {{-- Event header --}}
    @php $overall = $event->overallReadiness(); $dc = \App\Models\Department::ragClasses($overall); @endphp

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="font-semibold text-gray-900 text-lg">{{ $event->name }}</h2>
                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ $event->type }}</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dc['bg'] }} {{ $dc['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $dc['bar'] }}"></span>
                        {{ $overall }}% — {{ \App\Models\Department::ragStatus($overall) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $event->dateRangeLabel() }}
                    @if ($event->event_time) &bull; {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }} @endif
                    @if ($event->venue) &bull; {{ $event->venue }} @endif
                    @if ($event->client) &bull; {{ $event->client }} @endif
                </p>
                @if ($event->schedule_category || $event->coordinator || $event->totalParticipants())
                    <p class="text-xs text-gray-500 mt-2">
                        @if ($event->schedule_category) {{ $event->schedule_category->label() }} @endif
                        @if ($event->schedule_reference) &bull; {{ $event->schedule_reference }} @endif
                        @if ($event->coordinator) &bull; Coordinator: {{ $event->coordinator }} @endif
                        @if ($event->programme_assistant) &bull; PA: {{ $event->programme_assistant }} @endif
                        @if ($event->totalParticipants()) &bull; {{ $event->totalParticipants() }} participants @endif
                        @if ($event->package_remarks) &bull; {{ $event->package_remarks }} @endif
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.events.edit', $event) }}"
                    class="text-sm border border-gray-300 hover:border-gray-400 text-gray-700 px-3 py-1.5 rounded-lg font-medium transition-colors">
                    Edit Event
                </a>
                <a href="{{ route('events.print', $event) }}"
                    class="text-sm border border-gray-300 hover:border-gray-400 text-gray-700 px-3 py-1.5 rounded-lg font-medium transition-colors">
                    Print Report
                </a>
                <a href="{{ route('events.export', $event) }}"
                    class="text-sm border border-gray-300 hover:border-gray-400 text-gray-700 px-3 py-1.5 rounded-lg font-medium transition-colors">
                    Export CSV
                </a>
            </div>
        </div>
        <div class="mt-4 bg-gray-200 rounded-full h-2 overflow-hidden">
            <div class="{{ $dc['bar'] }} h-2 rounded-full transition-all" style="width: {{ $overall }}%"></div>
        </div>
    </div>

    {{-- Accommodation Bookings --}}
    @if ($event->accommodations->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Accommodation Bookings
            </h3>

            <div class="space-y-3">
                @foreach ($event->accommodations as $booking)
                    <div class="border border-gray-200 rounded-lg p-4 {{ $booking->requires_outsourcing ? 'bg-amber-50 border-amber-300' : 'bg-white' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-medium text-gray-900">{{ $booking->hostel->name }}</h4>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                        {{ $booking->residency_type }}
                                    </span>
                                    @if ($booking->requires_outsourcing)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-medium">
                                            ⚠️ Outsourcing Required
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2 grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500">Rooms Booked</p>
                                        <p class="font-semibold text-gray-900">{{ $booking->rooms_booked }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Remaining After Booking</p>
                                        <p class="font-semibold {{ $booking->rooms_available_after_booking <= 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $booking->rooms_available_after_booking }} rooms
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Hostel Capacity</p>
                                        <p class="font-semibold text-gray-900">{{ $booking->hostel->capacity }} rooms</p>
                                    </div>
                                </div>
                                @if ($booking->notes)
                                    <p class="text-xs text-gray-600 mt-2">
                                        <span class="font-medium">Note:</span> {{ $booking->notes }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $totalBooked = $event->accommodations->sum('rooms_booked');
                $requiresOutsourcing = $event->accommodations->where('requires_outsourcing', true)->count() > 0;
            @endphp

            @if ($requiresOutsourcing)
                <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                    <p class="font-medium">⚠️ Outsourcing Alert</p>
                    <p class="text-xs mt-1">Some bookings exceed hostel capacity. Consider outsourcing accommodation for this event.</p>
                </div>
            @else
                <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
                    <p class="font-medium">✓ Accommodation Confirmed</p>
                    <p class="text-xs mt-1">All {{ $totalBooked }} rooms successfully booked within available capacity.</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Department requirement cards --}}
    <div class="space-y-4">
        @foreach ($departmentRequirements as $dr)
            @php $drc = \App\Models\Department::ragClasses($dr['pct']); @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- Dept header --}}
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $dr['dept']->color }}"></div>
                        <h3 class="font-semibold text-gray-900">{{ $dr['dept']->name }}</h3>
                        <span class="text-xs text-gray-400">{{ $dr['completed'] }}/{{ $dr['total'] }} done</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $drc['bg'] }} {{ $drc['text'] }}">
                            {{ $dr['pct'] }}%
                        </span>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="px-5 pb-3">
                    <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $drc['bar'] }} h-1.5 rounded-full" style="width: {{ $dr['pct'] }}%"></div>
                    </div>
                </div>

                {{-- Requirements list --}}
                @if ($dr['reqs']->isNotEmpty())
                    <ul class="border-t border-gray-100 divide-y divide-gray-50">
                        @foreach ($dr['reqs'] as $req)
                            <li class="px-5 py-3 flex items-start gap-3">
                                <div class="mt-0.5 w-4 h-4 rounded border-2 flex items-center justify-center shrink-0
                                    {{ $req->is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 bg-white' }}">
                                    @if ($req->is_completed)
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start gap-2 flex-wrap">
                                        <p class="text-sm text-gray-800 {{ $req->is_completed ? 'line-through text-gray-400' : '' }}">
                                            {{ $req->description }}
                                        </p>
                                        <x-priority-badge :priority="$req->priority" />
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-3 mt-0.5">
                                        @if ($req->responsible_officer)
                                            <p class="text-xs text-gray-400">{{ $req->responsible_officer }}</p>
                                        @endif
                                        @if ($req->deadline)
                                            <p class="text-xs {{ $req->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                                Due {{ $req->deadline->format('d M Y') }}{{ $req->isOverdue() ? ' — OVERDUE' : '' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>                                <form method="POST"
                                    action="{{ route('admin.events.requirements.remove', [$event, $req]) }}"
                                    onsubmit="return confirm('Remove this requirement?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-300 hover:text-red-400 transition-colors mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- Add requirement form --}}
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                    <form method="POST" action="{{ route('admin.events.requirements.add', $event) }}" class="flex flex-wrap gap-2">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $dr['dept']->id }}">
                        <input type="text" name="description" placeholder="Add a requirement for {{ $dr['dept']->name }}…"
                            class="flex-1 min-w-48 text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            required>
                        <select name="priority"
                            class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @foreach (\App\Enums\Priority::cases() as $p)
                                <option value="{{ $p->value }}" @selected($p->value === 'medium')>
                                    {{ match($p->value) { 'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', 'low' => '⚪' } }} {{ $p->label() }}
                                </option>
                            @endforeach
                        </select>
                        <input type="date" name="deadline"
                            class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <input type="text" name="responsible_officer" placeholder="Officer"
                            class="w-36 text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Add
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
