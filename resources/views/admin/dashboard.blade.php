<x-layouts.admin>
    <x-slot:title>Overview</x-slot:title>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total Staff', 'value' => $totalUsers, 'sub' => $pendingApprovals.' pending approval', 'color' => 'indigo'],
            ['label' => 'Total Events', 'value' => $totalEvents, 'sub' => count($upcomingEvents).' upcoming', 'color' => 'blue'],
            ['label' => 'Avg. Readiness', 'value' => $avgReadiness.'%', 'sub' => 'Current week', 'color' => $avgReadiness >= 80 ? 'green' : ($avgReadiness >= 40 ? 'amber' : 'red')],
            ['label' => 'Completion Rate', 'value' => $completionRate.'%', 'sub' => 'All requirements', 'color' => $completionRate >= 80 ? 'green' : ($completionRate >= 40 ? 'amber' : 'red')],
        ] as $card)
            @php
                $colors = [
                    'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'val' => 'text-indigo-800'],
                    'blue'   => ['bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'val' => 'text-blue-800'],
                    'green'  => ['bg' => 'bg-green-50',  'text' => 'text-green-700',  'val' => 'text-green-800'],
                    'amber'  => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'val' => 'text-amber-800'],
                    'red'    => ['bg' => 'bg-red-50',    'text' => 'text-red-700',    'val' => 'text-red-800'],
                ];
                $c = $colors[$card['color']];
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                <p class="text-3xl font-bold {{ $c['val'] }} mt-1">{{ $card['value'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Visitor chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Page Visits — Last 7 Days</h2>
                <div class="text-xs text-gray-500">
                    Today: <span class="font-semibold text-gray-800">{{ $visitorsToday }}</span>
                    &nbsp;|&nbsp; This week: <span class="font-semibold text-gray-800">{{ $visitorsThisWeek }}</span>
                </div>
            </div>
            @php $maxVisits = max($visitsByDay->pluck('count')->max(), 1); @endphp
            <div class="flex items-end gap-2 h-32">
                @foreach ($visitsByDay as $day)
                    @php $height = max(4, (int) round(($day['count'] / $maxVisits) * 100)); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[10px] text-gray-400">{{ $day['count'] }}</span>
                        <div class="w-full bg-indigo-500 rounded-t transition-all"
                             style="height: {{ $height }}%"></div>
                        <span class="text-[10px] text-gray-500">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-right">
                <a href="{{ route('admin.visitors.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">View full report →</a>
            </div>
        </div>

        {{-- Upcoming events --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Upcoming Events</h2>
                <a href="{{ route('admin.events.create') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">+ Add</a>
            </div>
            @if ($upcomingEvents->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">No upcoming events</p>
            @else
                <div class="space-y-3">
                    @foreach ($upcomingEvents as $event)
                        @php $overall = $event->overallReadiness(); $dc = \App\Models\Department::ragClasses($overall); @endphp
                        <a href="{{ route('admin.events.show', $event) }}"
                            class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-9 h-9 rounded-lg {{ $dc['bg'] }} flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 {{ $dc['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-600">{{ $event->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $event->event_date->format('d M Y') }}</p>
                                <div class="mt-1.5 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $dc['bar'] }} h-1.5 rounded-full" style="width: {{ $overall }}%"></div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold {{ $dc['text'] }} shrink-0">{{ $overall }}%</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Department readiness --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Department Readiness — Current Week</h2>
            @if ($deptReadiness->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">No data for current week</p>
            @else
                <div class="space-y-3">
                    @foreach ($deptReadiness as $dr)
                        @php $dc = \App\Models\Department::ragClasses($dr['percentage']); @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $dr['department']->color }}"></div>
                            <span class="text-sm text-gray-700 w-36 truncate">{{ $dr['department']->name }}</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="{{ $dc['bar'] }} h-2 rounded-full transition-all" style="width: {{ $dr['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $dc['text'] }} w-10 text-right">{{ $dr['percentage'] }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Quick actions --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="space-y-2">
                @foreach ([
                    ['route' => 'admin.events.create', 'label' => 'Add New Event', 'color' => 'bg-indigo-600 hover:bg-indigo-700 text-white'],
                    ['route' => 'admin.users.index',   'label' => 'Review Approvals', 'color' => 'bg-amber-500 hover:bg-amber-600 text-white'],
                    ['route' => 'admin.staff.index',   'label' => 'View Staff Performance', 'color' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300'],
                    ['route' => 'admin.visitors.index','label' => 'Visitor Report', 'color' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300'],
                ] as $action)
                    <a href="{{ route($action['route']) }}"
                        class="block w-full text-center text-sm font-medium px-4 py-2.5 rounded-lg transition-colors {{ $action['color'] }}">
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.admin>
