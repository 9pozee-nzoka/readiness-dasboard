<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Readiness Report — {{ $event->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            @page { margin: 15mm 12mm; size: A4; }
            body { font-size: 11pt; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            table { page-break-inside: avoid; }
        }
        body { font-family: 'Instrument Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-white text-gray-900 p-8 max-w-5xl mx-auto">

    {{-- Print / Back controls --}}
    <div class="no-print flex items-center gap-3 mb-6">
        <a href="{{ route('events.show', $event) }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            ← Back to event
        </a>
        <button onclick="window.print()"
            class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            Print / Save as PDF
        </button>
        <a href="{{ route('reports.weekly-csv', ['week' => $event->planning_week_id]) }}"
            class="border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
            Export Weekly CSV
        </a>
    </div>

    {{-- ── Cover ──────────────────────────────────────────────────── --}}
    <div class="border-b-2 border-gray-900 pb-6 mb-8">
        <div class="flex items-start gap-4">
            <div class="flex items-center gap-0 shrink-0">
                <img src="{{ asset('assets/logos/1.png') }}" alt="" class="h-16 w-auto object-contain">
                <img src="{{ asset('assets/logos/2.png') }}" alt="" class="h-16 w-auto object-contain">
                <img src="{{ asset('assets/logos/3.png') }}" alt="" class="h-16 w-auto object-contain">
            </div>
            <div class="flex-1">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Event Readiness Report</p>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $event->name }}</h1>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-gray-600">
                    <span><strong>Date:</strong> {{ $event->event_date->format('l, d F Y') }}</span>
                    @if ($event->venue) <span><strong>Venue:</strong> {{ $event->venue }}</span> @endif
                    <span><strong>Type:</strong> {{ $event->type }}</span>
                    @if ($event->coordinator) <span><strong>Coordinator:</strong> {{ $event->coordinator }}</span> @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Summary metrics ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-4 gap-4 mb-8">
        @foreach ([
            ['label' => 'Overall Readiness', 'value' => $overall.'%',
             'color' => $overall === 100 ? 'text-green-700' : ($overall > 0 ? 'text-amber-700' : 'text-red-700')],
            ['label' => 'Weighted Readiness', 'value' => $weighted.'%',
             'color' => $weighted === 100 ? 'text-green-700' : ($weighted > 0 ? 'text-amber-700' : 'text-red-700')],
            ['label' => 'Risk Score', 'value' => $riskScore.'/100',
             'color' => $riskScore >= 70 ? 'text-red-700' : ($riskScore >= 45 ? 'text-orange-700' : ($riskScore >= 20 ? 'text-amber-700' : 'text-green-700'))],
            ['label' => 'Critical Pending', 'value' => $criticalPending,
             'color' => $criticalPending > 0 ? 'text-red-700' : 'text-green-700'],
        ] as $m)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold {{ $m['color'] }}">{{ $m['value'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $m['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Readiness bar by dept ───────────────────────────────────── --}}
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-widest mb-3">Department Readiness Overview</h2>
        <div class="space-y-2">
            @foreach ($departmentReadiness->sortByDesc('pct') as $dr)
                @php $dc = \App\Models\Department::ragClasses($dr['pct']); @endphp
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dr['dept']->color }}"></div>
                    <span class="text-sm text-gray-700 w-36 truncate shrink-0">{{ $dr['dept']->name }}</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="{{ $dc['bar'] }} h-2 rounded-full" style="width: {{ $dr['pct'] }}%"></div>
                    </div>
                    <span class="text-sm font-semibold {{ $dc['text'] }} w-12 text-right shrink-0">{{ $dr['pct'] }}%</span>
                    <span class="text-xs text-gray-400 shrink-0">{{ $dr['completed'] }}/{{ $dr['total'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Requirements detail per department ─────────────────────── --}}
    @foreach ($departmentReadiness as $dr)
        <div class="mb-8 {{ ! $loop->first ? 'page-break' : '' }}">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-200">
                <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $dr['dept']->color }}"></div>
                <h2 class="font-semibold text-gray-900">{{ $dr['dept']->name }}</h2>
                <span class="ml-auto text-sm font-semibold
                    {{ $dr['pct'] === 100 ? 'text-green-700' : ($dr['pct'] > 0 ? 'text-amber-700' : 'text-red-700') }}">
                    {{ $dr['pct'] }}% — {{ \App\Models\Department::ragStatus($dr['pct']) }}
                </span>
            </div>

            <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase tracking-wide">
                        <th class="px-3 py-2 text-left font-semibold w-8">#</th>
                        <th class="px-3 py-2 text-left font-semibold">Requirement</th>
                        <th class="px-3 py-2 text-left font-semibold w-20">Priority</th>
                        <th class="px-3 py-2 text-left font-semibold w-24">Deadline</th>
                        <th class="px-3 py-2 text-left font-semibold w-28">Officer</th>
                        <th class="px-3 py-2 text-left font-semibold w-20">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($dr['reqs'] as $i => $req)
                        <tr class="{{ $req->is_completed ? 'bg-green-50/30' : ($req->isOverdue() ? 'bg-red-50/30' : '') }}">
                            <td class="px-3 py-2 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-2 text-gray-800 {{ $req->is_completed ? 'line-through text-gray-400' : '' }}">
                                {{ $req->description }}
                            </td>
                            <td class="px-3 py-2">
                                @php $pc = $req->priority->classes(); @endphp
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $pc['bg'] }} {{ $pc['text'] }} {{ $pc['border'] }}">
                                    {{ $req->priority->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-2 {{ $req->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                {{ $req->deadline?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-gray-500 truncate max-w-[100px]">{{ $req->responsible_officer ?? '—' }}</td>
                            <td class="px-3 py-2">
                                @if ($req->is_completed)
                                    <span class="text-green-700 font-semibold">✓ Done</span>
                                @elseif ($req->isOverdue())
                                    <span class="text-red-600 font-semibold">Overdue</span>
                                @else
                                    <span class="text-amber-700">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    {{-- Footer --}}
    <div class="border-t border-gray-200 pt-4 mt-8 flex items-center justify-between text-xs text-gray-400">
        <span>Generated {{ now()->format('d M Y H:i') }} · Weekly Event Readiness Dashboard</span>
        <span>{{ $event->planningWeek?->label }}</span>
    </div>

</body>
</html>
