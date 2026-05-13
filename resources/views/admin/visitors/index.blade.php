<x-layouts.admin>
    <x-slot:title>Visitor Tracking</x-slot:title>

    {{-- Range selector --}}
    <div class="flex items-center gap-2 mb-6">
        @foreach (['1' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days'] as $val => $label)
            <a href="{{ route('admin.visitors.index', ['range' => $val]) }}"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors
                    {{ $range === $val ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total Visits', 'value' => number_format($totalVisits)],
            ['label' => 'Unique IPs', 'value' => number_format($uniqueIps)],
            ['label' => 'Logged-in Users', 'value' => number_format($loggedIn)],
            ['label' => 'Anonymous', 'value' => number_format($anonymous)],
        ] as $card)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Bar chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Visits per Day</h3>
            @php $maxV = max($visitsByDay->pluck('count')->max(), 1); @endphp
            <div class="flex items-end gap-1.5 h-36">
                @foreach ($visitsByDay as $day)
                    @php $h = max(4, (int) round(($day['count'] / $maxV) * 100)); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[10px] text-gray-400">{{ $day['count'] }}</span>
                        <div class="w-full bg-indigo-500 rounded-t" style="height: {{ $h }}%"></div>
                        <span class="text-[10px] text-gray-500 truncate w-full text-center">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top pages --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Top Pages</h3>
            @if ($topPages->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">No data</p>
            @else
                <div class="space-y-2">
                    @foreach ($topPages as $page)
                        @php $pct = $totalVisits > 0 ? (int) round(($page->visits / $totalVisits) * 100) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-0.5">
                                <span class="text-gray-700 truncate max-w-[160px]" title="{{ $page->path }}">{{ $page->path }}</span>
                                <span class="text-gray-500 shrink-0 ml-2">{{ $page->visits }}</span>
                            </div>
                            <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-indigo-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent visits --}}
        <div class="lg:col-span-3 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Visits</h3>
            </div>
            @if ($recentVisits->isEmpty())
                <p class="text-sm text-gray-400 text-center py-10">No visits recorded yet</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                <th class="px-5 py-3 text-left">Time</th>
                                <th class="px-5 py-3 text-left">User</th>
                                <th class="px-5 py-3 text-left">Page</th>
                                <th class="px-5 py-3 text-left">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($recentVisits as $visit)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                                        {{ $visit->visited_at->format('d M H:i:s') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($visit->user)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-[10px] shrink-0">
                                                    {{ mb_strtoupper(mb_substr($visit->user->name, 0, 1)) }}
                                                </div>
                                                <span class="text-gray-700 text-xs">{{ $visit->user->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs">Anonymous</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 text-xs font-mono max-w-xs truncate">
                                        {{ $visit->path }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-500 text-xs font-mono">
                                        {{ $visit->ip_address ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
