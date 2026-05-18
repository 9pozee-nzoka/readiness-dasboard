<x-layouts.app>
    <x-slot:title>Director Overview</x-slot:title>

    {{-- ── Hero banner ──────────────────────────────────────────────── --}}
    <div class="relative -mx-4 sm:-mx-6 lg:-mx-8 -mt-8 mb-8 overflow-hidden">
        <div class="bg-gradient-to-br from-slate-800 via-slate-900 to-indigo-950 px-6 sm:px-10 lg:px-12 pt-10 pb-16">

            {{-- Week selector top-right --}}
            <div class="absolute top-5 right-6">
                <form method="GET" action="{{ route('dashboard.index') }}" id="week-form">
                    <select name="week" onchange="document.getElementById('week-form').submit()"
                        class="rounded-lg border border-white/20 bg-white/10 text-white text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-white/30 backdrop-blur-sm">
                        @foreach ($weeks as $week)
                            <option value="{{ $week->id }}" @selected($week->id === $selectedWeek?->id)
                                class="text-gray-900 bg-white">
                                {{ $week->label }}{{ $week->is_current ? ' ✦' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Greeting --}}
            <div class="mb-8">
                <p class="text-indigo-300 text-sm font-medium tracking-wide uppercase mb-1">Director Overview</p>
                <h1 class="text-3xl font-bold text-white">
                    {{ $selectedWeek?->label ?? 'Event Readiness' }}
                </h1>
                <p class="text-slate-400 text-sm mt-1">
                    {{ now()->format('l, d F Y') }}
                    @if ($selectedWeek)
                        &nbsp;·&nbsp; {{ $selectedWeek->week_start->format('d M') }} – {{ $selectedWeek->week_end->format('d M Y') }}
                    @endif
                </p>
            </div>

            {{-- KPI tiles --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach ([
                    ['label' => 'Total Events',    'value' => $totalEvents,           'sub' => 'this week',          'accent' => 'text-indigo-300',  'bg' => 'bg-indigo-500/20'],
                    ['label' => 'Fully Ready',      'value' => $fullyReady,            'sub' => 'events at 100%',     'accent' => 'text-emerald-300', 'bg' => 'bg-emerald-500/20'],
                    ['label' => 'In Progress',      'value' => $inProgress,            'sub' => 'events ongoing',     'accent' => 'text-amber-300',   'bg' => 'bg-amber-500/20'],
                    ['label' => 'Not Started',      'value' => $notStarted,            'sub' => 'events at 0%',       'accent' => 'text-red-300',     'bg' => 'bg-red-500/20'],
                    ['label' => 'Avg. Readiness',   'value' => $averageReadiness.'%',  'sub' => 'across all events',  'accent' => 'text-sky-300',     'bg' => 'bg-sky-500/20'],
                    ['label' => 'At Risk',          'value' => $atRiskEvents->count(), 'sub' => 'critical issues',    'accent' => 'text-rose-300',    'bg' => 'bg-rose-500/20'],
                ] as $kpi)
                    <div class="rounded-xl {{ $kpi['bg'] }} border border-white/10 px-4 py-3 backdrop-blur-sm">
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">{{ $kpi['label'] }}</p>
                        <p class="text-2xl font-bold {{ $kpi['accent'] }}">{{ $kpi['value'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $kpi['sub'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Decorative bottom wave --}}
        <div class="absolute bottom-0 left-0 right-0 h-6 bg-gray-50"
             style="clip-path: ellipse(55% 100% at 50% 100%)"></div>
    </div>

    {{-- ── Alert strip ──────────────────────────────────────────────── --}}
    @if ($criticalAlerts->isNotEmpty() || $overdueCount > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap mb-2">
                    <h3 class="font-semibold text-red-800 text-sm">Critical Alerts Requiring Attention</h3>
                    @if ($overdueCount > 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-200 text-red-800">
                            {{ $overdueCount }} overdue
                        </span>
                    @endif
                    @if ($criticalAlerts->isNotEmpty())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                            {{ $criticalAlerts->count() }} critical unresolved
                        </span>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                    @foreach ($criticalAlerts->take(6) as $alert)
                        <div class="flex items-start gap-2 text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0 mt-1"></span>
                            <div class="flex-1 min-w-0">
                                <span class="text-red-800 font-medium">{{ $alert->description }}</span>
                                <span class="text-red-500 ml-1">
                                    — {{ $alert->event->name }}
                                    @if ($alert->department) · {{ $alert->department->name }} @endif
                                    @if ($alert->deadline)
                                        · <span class="{{ $alert->isOverdue() ? 'font-bold' : '' }}">
                                            Due {{ $alert->deadline->format('d M') }}{{ $alert->isOverdue() ? ' ⚠' : '' }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            <a href="{{ route('events.show', $alert->event) }}"
                                class="text-red-600 hover:text-red-800 font-semibold shrink-0">→</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ── Main grid ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Completion ring + stats ──────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col items-center justify-center gap-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide self-start">Overall Completion</h2>

            {{-- SVG donut ring --}}
            @php
                $radius      = 54;
                $circumference = 2 * M_PI * $radius;
                $offset      = $circumference - ($completionRate / 100) * $circumference;
                $ringColor   = $completionRate === 100 ? '#22c55e' : ($completionRate >= 50 ? '#f59e0b' : '#ef4444');
            @endphp
            <div class="relative w-36 h-36">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 128 128">
                    <circle cx="64" cy="64" r="{{ $radius }}" fill="none" stroke="#e5e7eb" stroke-width="12"/>
                    <circle cx="64" cy="64" r="{{ $radius }}" fill="none"
                        stroke="{{ $ringColor }}" stroke-width="12"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        style="transition: stroke-dashoffset 1s ease"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold text-gray-900">{{ $completionRate }}%</span>
                    <span class="text-xs text-gray-400">complete</span>
                </div>
            </div>

            <div class="w-full grid grid-cols-2 gap-3 text-center">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xl font-bold text-gray-900">{{ $completedReqs }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Tasks done</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xl font-bold text-gray-900">{{ $totalReqs - $completedReqs }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Remaining</p>
                </div>
                <div class="bg-red-50 rounded-xl p-3">
                    <p class="text-xl font-bold text-red-700">{{ $criticalTotal - $criticalDone }}</p>
                    <p class="text-xs text-red-500 mt-0.5">Critical pending</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-3">
                    <p class="text-xl font-bold text-amber-700">{{ $overdueCount }}</p>
                    <p class="text-xs text-amber-500 mt-0.5">Overdue</p>
                </div>
            </div>
        </div>

        {{-- Event timeline ───────────────────────────────────────── --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Event Timeline</h2>

            @if ($events->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-300">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm">No events this week</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($events as $event)
                        @php
                            $overall  = $event->overallReadiness();
                            $weighted = $event->weightedReadiness();
                            $dc       = \App\Models\Department::ragClasses($overall);
                            $cp       = $event->criticalPendingCount();
                        @endphp
                        <a href="{{ route('events.show', $event) }}"
                            class="group flex items-center gap-4 p-4 rounded-xl border {{ $event->isAtRisk() ? 'border-red-200 bg-red-50/40' : 'border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/30' }} transition-all">

                            {{-- Date badge --}}
                            <div class="shrink-0 w-12 text-center">
                                <p class="text-xs font-semibold text-gray-400 uppercase">{{ $event->event_date->format('M') }}</p>
                                <p class="text-2xl font-bold text-gray-800 leading-none">{{ $event->event_date->format('d') }}</p>
                                <p class="text-xs text-gray-400">{{ $event->event_date->format('D') }}</p>
                            </div>

                            {{-- Divider --}}
                            <div class="w-px h-12 bg-gray-200 shrink-0"></div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-gray-900 text-sm group-hover:text-indigo-700 transition-colors truncate">
                                        {{ $event->name }}
                                    </p>
                                    @if ($event->isAtRisk())
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">⚠ At Risk</span>
                                    @endif
                                    @if ($cp > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-600 border border-red-200">
                                            {{ $cp }} critical
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $event->type }}
                                    @if ($event->venue) · {{ $event->venue }} @endif
                                    @if ($event->event_time) · {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }} @endif
                                </p>
                                {{-- Dual progress --}}
                                <div class="mt-2 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="{{ $dc['bar'] }} h-1.5 rounded-full transition-all" style="width: {{ $overall }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold {{ $dc['text'] }} w-8 text-right">{{ $overall }}%</span>
                                    </div>
                                    @php $wc = \App\Models\Department::ragClasses($weighted); @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-1 overflow-hidden">
                                            <div class="{{ $wc['bar'] }} h-1 rounded-full opacity-70 transition-all" style="width: {{ $weighted }}%"></div>
                                        </div>
                                        <span class="text-[10px] text-gray-400 w-8 text-right">{{ $weighted }}% w</span>
                                    </div>
                                </div>
                            </div>

                            {{-- RAG badge --}}
                            <div class="shrink-0">
                                <x-rag-badge :percentage="$overall" />
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Department readiness grid ────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6"
         x-data="deptPanel()">

        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Department Readiness</h2>
            <span class="text-xs text-gray-400">{{ $deptReadiness->count() }} departments tracked · <span class="text-indigo-500 font-medium">click a card to drill down</span></span>
        </div>

        @if ($deptReadiness->isEmpty())
            <p class="text-center text-gray-400 text-sm py-8">No department data for this week.</p>
        @else
            {{-- Cards grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach ($deptReadiness as $index => $dr)
                    @php
                        $dc     = $dr['classes'];
                        $pct    = $dr['percentage'];
                        $r      = 28;
                        $circ   = 2 * M_PI * $r;
                        $off    = $circ - ($pct / 100) * $circ;
                        $stroke = $pct === 100 ? '#22c55e' : ($pct > 0 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <button type="button"
                        @click="open({{ $index }})"
                        class="group relative flex flex-col items-center gap-2 p-4 rounded-xl border transition-all text-left w-full
                            {{ $dr['criticalPending'] > 0 ? 'border-red-200 bg-red-50/30' : 'border-gray-100 bg-gray-50/50' }}
                            hover:border-indigo-300 hover:bg-indigo-50/40 hover:shadow-md cursor-pointer"
                        :class="activeIndex === {{ $index }} ? 'border-indigo-400 bg-indigo-50 shadow-md ring-2 ring-indigo-200' : ''">

                        {{-- Dept colour dot --}}
                        <div class="absolute top-3 left-3 w-2 h-2 rounded-full"
                             style="background-color: {{ $dr['department']->color }}"></div>

                        {{-- Expand indicator --}}
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity"
                             :class="activeIndex === {{ $index }} ? 'opacity-100' : ''">
                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>

                        {{-- Mini donut --}}
                        <div class="relative w-16 h-16 mt-1">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 64 64">
                                <circle cx="32" cy="32" r="{{ $r }}" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                                <circle cx="32" cy="32" r="{{ $r }}" fill="none"
                                    stroke="{{ $stroke }}" stroke-width="6"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $circ }}"
                                    stroke-dashoffset="{{ $off }}"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-sm font-bold text-gray-800">{{ $pct }}%</span>
                            </div>
                        </div>

                        {{-- Name + status --}}
                        <div class="text-center">
                            <p class="text-xs font-semibold text-gray-800 leading-tight">{{ $dr['department']->name }}</p>
                            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $dc['bg'] }} {{ $dc['text'] }}">
                                <span class="w-1 h-1 rounded-full {{ $dc['bar'] }}"></span>
                                {{ $dr['status'] }}
                            </span>
                        </div>

                        {{-- Alerts --}}
                        @if ($dr['criticalPending'] > 0 || $dr['overdue'] > 0)
                            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                                @if ($dr['criticalPending'] > 0)
                                    <span class="text-[10px] font-semibold text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded">
                                        {{ $dr['criticalPending'] }} critical
                                    </span>
                                @endif
                                @if ($dr['overdue'] > 0)
                                    <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                                        {{ $dr['overdue'] }} overdue
                                    </span>
                                @endif
                            </div>
                        @endif

                        <p class="text-[10px] text-gray-400">{{ $dr['completed'] }}/{{ $dr['total'] }} tasks</p>
                    </button>
                @endforeach
            </div>

            {{-- ── Drill-down panel ── --}}
            <div x-show="activeIndex !== null" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="mt-5 rounded-2xl border border-indigo-200 bg-indigo-50/30 overflow-hidden">

                {{-- Panel header --}}
                <div class="flex items-center justify-between px-5 py-4 bg-white border-b border-indigo-100">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full shrink-0" :style="'background-color:' + activeDept.color"></div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm" x-text="activeDept.name + ' — Event Breakdown'"></h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Readiness per event this week
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="close()"
                        class="w-7 h-7 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Event rows --}}
                <div class="p-4">
                    <template x-if="activeBreakdown.length === 0">
                        <p class="text-center text-gray-400 text-sm py-6">No requirements for this department this week.</p>
                    </template>

                    <div class="space-y-3">
                        <template x-for="eb in activeBreakdown" :key="eb.event_id">
                            <a :href="eb.event_url"
                               class="group flex items-center gap-4 bg-white rounded-xl border border-gray-100 hover:border-indigo-200 hover:shadow-sm px-4 py-3.5 transition-all">

                                {{-- Date badge --}}
                                <div class="shrink-0 w-10 text-center">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase" x-text="eb.month"></p>
                                    <p class="text-xl font-bold text-gray-800 leading-none" x-text="eb.day"></p>
                                </div>

                                <div class="w-px h-10 bg-gray-100 shrink-0"></div>

                                {{-- Event info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors truncate"
                                           x-text="eb.event_name"></p>
                                        <template x-if="eb.critical > 0">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700"
                                                  x-text="eb.critical + ' critical'"></span>
                                        </template>
                                        <template x-if="eb.overdue > 0">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200"
                                                  x-text="eb.overdue + ' overdue'"></span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="eb.event_type + (eb.venue ? ' · ' + eb.venue : '')"></p>

                                    {{-- Progress bar --}}
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full transition-all duration-500"
                                                 :class="eb.pct === 100 ? 'bg-green-500' : eb.pct > 0 ? 'bg-amber-400' : 'bg-red-400'"
                                                 :style="'width:' + eb.pct + '%'"></div>
                                        </div>
                                        <span class="text-xs font-semibold w-9 text-right shrink-0"
                                              :class="eb.pct === 100 ? 'text-green-700' : eb.pct > 0 ? 'text-amber-700' : 'text-red-600'"
                                              x-text="eb.pct + '%'"></span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5"
                                       x-text="eb.completed + '/' + eb.total + ' tasks completed'"></p>
                                </div>

                                {{-- Arrow --}}
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Readiness bar chart --}}
            <div class="mt-6 pt-5 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Readiness Comparison</p>
                <div class="space-y-2">
                    @foreach ($deptReadiness->sortByDesc('percentage') as $dr)
                        @php $dc = $dr['classes']; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dr['department']->color }}"></div>
                            <span class="text-xs text-gray-600 w-28 truncate shrink-0">{{ $dr['department']->name }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="{{ $dc['bar'] }} h-2 rounded-full transition-all duration-700"
                                     style="width: {{ $dr['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $dc['text'] }} w-9 text-right shrink-0">{{ $dr['percentage'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ── At-risk events detail ────────────────────────────────────── --}}
    @if ($atRiskEvents->isNotEmpty())        <div class="bg-white rounded-2xl border border-red-200 shadow-sm p-6">            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="font-semibold text-red-800 text-sm uppercase tracking-wide">
                    At-Risk Events ({{ $atRiskEvents->count() }})
                </h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($atRiskEvents as $event)
                    @php $overall = $event->overallReadiness(); $dc = \App\Models\Department::ragClasses($overall); @endphp
                    <a href="{{ route('events.show', $event) }}"
                        class="flex items-start gap-3 p-4 rounded-xl bg-red-50 border border-red-200 hover:bg-red-100 transition-colors group">
                        <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-red-900 text-sm group-hover:underline truncate">{{ $event->name }}</p>
                            <p class="text-xs text-red-600 mt-0.5">{{ $event->event_date->format('d M Y') }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex-1 bg-red-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $dc['bar'] }} h-1.5 rounded-full" style="width: {{ $overall }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-red-700">{{ $overall }}%</span>
                            </div>
                            <p class="text-[10px] text-red-500 mt-1">{{ $event->criticalPendingCount() }} critical task(s) unresolved</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @push('scripts')
    <style>[x-cloak] { display: none !important; }</style>
    <script>
        const deptData = @json($deptPanelData);

        function deptPanel() {
            return {
                activeIndex: null,
                activeDept: { name: '', color: '#6366f1' },
                activeBreakdown: [],

                open(index) {
                    if (this.activeIndex === index) {
                        this.close();
                        return;
                    }
                    this.activeIndex     = index;
                    this.activeDept      = { name: deptData[index].name, color: deptData[index].color };
                    this.activeBreakdown = deptData[index].breakdown;

                    this.$nextTick(() => {
                        const panel = this.$el.querySelector('[x-show]');
                        if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    });
                },

                close() {
                    this.activeIndex     = null;
                    this.activeDept      = { name: '', color: '#6366f1' };
                    this.activeBreakdown = [];
                },
            };
        }
    </script>
    @endpush

</x-layouts.app>
