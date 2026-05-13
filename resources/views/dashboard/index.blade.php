<x-layouts.app>
    <x-slot:title>Event Readiness Dashboard</x-slot:title>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Event Readiness Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Track departmental preparedness for all weekly events</p>
        </div>
        <form method="GET" action="{{ route('dashboard.index') }}" id="week-form">
            <label for="week" class="sr-only">Select Week</label>
            <select name="week" id="week" onchange="document.getElementById('week-form').submit()"
                class="block w-full sm:w-64 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                @foreach ($weeks as $week)
                    <option value="{{ $week->id }}" @selected($week->id === $selectedWeek?->id)>
                        {{ $week->label }}{{ $week->is_current ? ' (Current)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Summary metric cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <x-metric-card label="Total Events"    :value="$totalEvents"           icon="calendar"    color="indigo" />
        <x-metric-card label="Fully Ready"     :value="$fullyReady"            icon="check-circle" color="green" />
        <x-metric-card label="In Progress"     :value="$inProgress"            icon="clock"        color="amber" />
        <x-metric-card label="Not Started"     :value="$notStarted"            icon="x-circle"     color="red" />
        <x-metric-card label="Avg. Readiness"  :value="$averageReadiness . '%'" icon="chart-bar"   color="blue" />
    </div>

    {{-- Critical Alerts Panel --}}
    @if ($criticalAlerts->isNotEmpty() || $overdueCount > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap mb-2">
                        <h3 class="font-semibold text-red-800 text-sm">Critical Alerts</h3>
                        @if ($overdueCount > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-800">
                                {{ $overdueCount }} overdue task{{ $overdueCount !== 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>
                    <div class="space-y-1.5">
                        @foreach ($criticalAlerts as $alert)
                            <div class="flex items-start gap-2 text-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0 mt-1.5"></span>
                                <div class="flex-1 min-w-0">
                                    <span class="text-red-800 font-medium">{{ $alert->description }}</span>
                                    <span class="text-red-600 text-xs ml-2">
                                        — {{ $alert->event->name }}
                                        @if ($alert->department) · {{ $alert->department->name }} @endif
                                        @if ($alert->responsible_officer) · {{ $alert->responsible_officer }} @endif
                                        @if ($alert->deadline)
                                            · <span class="{{ $alert->isOverdue() ? 'font-semibold' : '' }}">
                                                Due {{ $alert->deadline->format('d M') }}{{ $alert->isOverdue() ? ' (OVERDUE)' : '' }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <a href="{{ route('events.show', $alert->event) }}"
                                    class="text-xs text-red-600 hover:text-red-800 font-medium shrink-0">View →</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Events list --}}
    @if ($events->isEmpty())
        <div class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500 font-medium">No events for this week</p>
            @if (auth()->user()->canManageEvents())
                <a href="{{ route('events.create') }}"
                    class="mt-4 inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add an event
                </a>
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach ($events as $event)
                @php
                    $overall  = $event->overallReadiness();
                    $weighted = $event->weightedReadiness();
                    $classes  = \App\Models\Department::ragClasses($overall);
                    $criticalPending = $event->criticalPendingCount();
                @endphp

                <div class="bg-white rounded-xl border {{ $event->isAtRisk() ? 'border-red-300' : 'border-gray-200' }} shadow-sm overflow-hidden">
                    {{-- Event header --}}
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 w-10 h-10 rounded-lg {{ $classes['bg'] }} flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 {{ $classes['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="font-semibold text-gray-900 text-base leading-tight">{{ $event->name }}</h2>
                                    @if ($event->isAtRisk())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-700 border border-red-200">
                                            ⚠ At Risk
                                        </span>
                                    @endif
                                    @if ($criticalPending > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-600 border border-red-200">
                                            {{ $criticalPending }} critical pending
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs text-gray-500">
                                    <span>{{ $event->event_date->format('D, d M Y') }}</span>
                                    @if ($event->event_time)
                                        <span>&bull; {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }}</span>
                                    @endif
                                    @if ($event->venue)
                                        <span>&bull; {{ $event->venue }}</span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-600 font-medium">
                                        {{ $event->type }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <x-rag-badge :percentage="$overall" />
                            <a href="{{ route('events.show', $event) }}"
                                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium whitespace-nowrap">
                                View Details →
                            </a>
                        </div>
                    </div>

                    {{-- Progress bars: simple + weighted --}}
                    <div class="px-6 pb-4 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400 w-20 shrink-0">Readiness</span>
                            <x-progress-bar :percentage="$overall" />
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400 w-20 shrink-0">Weighted</span>
                            @php $wc = \App\Models\Department::ragClasses($weighted); @endphp
                            <div class="flex-1 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $wc['bar'] }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $weighted }}%"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $wc['text'] }} w-10 text-right">{{ $weighted }}%</span>
                        </div>
                    </div>

                    {{-- Department mini-bars --}}
                    <details class="group">
                        <summary class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs font-medium text-gray-500 cursor-pointer hover:bg-gray-100 transition-colors list-none flex items-center justify-between">
                            <span>Department Breakdown</span>
                            <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="px-6 py-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($departments as $dept)
                                @php
                                    $pct = $dept->readinessForEvent($event->id);
                                    $dc  = \App\Models\Department::ragClasses($pct);
                                @endphp
                                @if ($event->requirements->where('department_id', $dept->id)->count() > 0)
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dept->color }}"></div>
                                        <span class="text-xs text-gray-600 w-32 truncate">{{ $dept->name }}</span>
                                        <div class="flex-1">
                                            <x-progress-bar :percentage="$pct" />
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </details>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
