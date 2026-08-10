<x-layouts.app>
    <x-slot:title>Recurring Issues</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recurring Issues</h1>
            <p class="text-sm text-gray-500 mt-1">Track patterns, root causes and fixes across events</p>
        </div>
        <a href="{{ route('recurring-issues.create') }}"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Log New Issue
        </a>
    </div>

    {{-- Stats strip --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total Issues',     'value' => $totalIssues,    'color' => 'text-gray-900',   'bg' => 'bg-white'],
            ['label' => 'Open Issues',      'value' => $openIssues,     'color' => 'text-amber-700',  'bg' => 'bg-amber-50'],
            ['label' => 'Critical Open',    'value' => $criticalIssues, 'color' => 'text-red-700',    'bg' => 'bg-red-50'],
            ['label' => 'Trending (30d)',   'value' => $trendingCount,  'color' => 'text-indigo-700', 'bg' => 'bg-indigo-50'],
        ] as $s)
            <div class="{{ $s['bg'] }} rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-2xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $s['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Top 5 by frequency --}}
    @if ($topIssues->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Most Frequent Open Issues</h2>
            <div class="space-y-2">
                @php $maxOcc = $topIssues->max('occurrences_count') ?: 1; @endphp
                @foreach ($topIssues as $ti)
                    @php $sc = $ti->severityClasses(); @endphp
                    <a href="{{ route('recurring-issues.show', $ti) }}"
                        class="flex items-center gap-3 group hover:bg-gray-50 rounded-lg px-2 py-1.5 transition-colors">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }} shrink-0 w-20 justify-center">
                            {{ ucfirst($ti->severity) }}
                        </span>
                        <span class="flex-1 text-sm text-gray-800 group-hover:text-indigo-700 truncate">{{ $ti->title }}</span>
                        <div class="w-24 bg-gray-200 rounded-full h-1.5 shrink-0">
                            <div class="{{ $sc['dot'] }} h-1.5 rounded-full" style="width: {{ round(($ti->occurrences_count / $maxOcc) * 100) }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-600 shrink-0 w-6 text-right">{{ $ti->occurrences_count }}×</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2 mb-5">
        <input type="text" name="search" value="{{ $search }}"
            placeholder="Search issues…"
            class="rounded-xl border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 w-56">

        <select name="category"
            class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            <option value="">All Categories</option>
            @foreach ($categories as $val => $label)
                <option value="{{ $val }}" @selected($category === $val)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="severity"
            class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            <option value="">All Severities</option>
            @foreach (['critical' => '🔴 Critical', 'high' => '🟠 High', 'medium' => '🟡 Medium', 'low' => '⚪ Low'] as $val => $label)
                <option value="{{ $val }}" @selected($severity === $val)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
            @foreach (['open' => 'Open', 'resolved' => 'Resolved', '' => 'All'] as $val => $label)
                <a href="{{ route('recurring-issues.index', array_filter(compact('search', 'category', 'severity') + ['status' => $val])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                        {{ $status === $val ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            Filter
        </button>
        @if ($search || $category || $severity)
            <a href="{{ route('recurring-issues.index') }}" class="self-center text-sm text-gray-400 hover:text-gray-600">Clear</a>
        @endif
    </form>

    {{-- Issues list --}}
    @if ($issues->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
            <p class="text-gray-500 font-medium">No recurring issues found</p>
            <p class="text-sm text-gray-400 mt-1">Start tracking patterns by logging your first issue</p>
            <a href="{{ route('recurring-issues.create') }}" class="mt-4 inline-block text-indigo-600 text-sm font-medium hover:underline">Log an issue →</a>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
<table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-left">Issue</th>
                        <th class="px-5 py-3 text-left">Category</th>
                        <th class="px-5 py-3 text-left">Department</th>
                        <th class="px-5 py-3 text-center">Times</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Logged</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($issues as $issue)
                        @php $sc = $issue->severityClasses(); @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('recurring-issues.show', $issue) }}"
                                    class="font-medium text-gray-900 hover:text-indigo-700 transition-colors">
                                    {{ $issue->title }}
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                        {{ ucfirst($issue->severity) }}
                                    </span>
                                    @if ($issue->isTrending())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                                            🔥 Trending
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                                    {{ $categories[$issue->category] ?? $issue->category }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($issue->department)
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $issue->department->color }}"></div>
                                        <span class="text-xs text-gray-600">{{ $issue->department->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Organisation-wide</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                    {{ $issue->occurrences_count >= 5 ? 'bg-red-100 text-red-700' : ($issue->occurrences_count >= 3 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $issue->occurrences_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($issue->is_resolved)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Resolved
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Open
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-400">
                                {{ $issue->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
</div>
            @if ($issues->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $issues->links() }}</div>
            @endif
        </div>
    @endif
</x-layouts.app>
