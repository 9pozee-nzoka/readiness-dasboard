<x-layouts.admin>
    <x-slot:title>Staff & KPI Performance</x-slot:title>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff & KPI Performance</h1>
            <p class="text-sm text-gray-500 mt-1">Department KPIs, individual completion rates, SLA compliance and rankings</p>
        </div>
        <a href="{{ route('admin.staff.create') }}"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Staff
        </a>
    </div>

    {{-- ── Department KPI cards ─────────────────────────────────── --}}
    <div class="mb-8">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Department KPI Summary</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
            @foreach ($deptKpis as $dk)
                @php $dc = \App\Models\Department::ragClasses($dk['rate']); @endphp
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $dk['dept']->color }}"></div>
                        <span class="text-xs font-semibold text-gray-700 truncate">{{ $dk['dept']->name }}</span>
                    </div>

                    {{-- Readiness ring --}}
                    @php
                        $r = 18; $circ = 2 * M_PI * $r;
                        $off = $circ - ($dk['rate'] / 100) * $circ;
                        $stroke = $dk['rate'] === 100 ? '#22c55e' : ($dk['rate'] > 0 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="relative w-12 h-12 shrink-0">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 44 44">
                                <circle cx="22" cy="22" r="{{ $r }}" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                                <circle cx="22" cy="22" r="{{ $r }}" fill="none" stroke="{{ $stroke }}" stroke-width="4"
                                    stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[10px] font-bold text-gray-800">{{ $dk['rate'] }}%</span>
                            </div>
                        </div>
                        <div class="text-xs space-y-0.5">
                            <p class="text-gray-500">{{ $dk['completed'] }}/{{ $dk['total'] }} done</p>
                            @if ($dk['overdue'] > 0)
                                <p class="text-red-600 font-semibold">{{ $dk['overdue'] }} overdue</p>
                            @endif
                            @if ($dk['critical'] > 0)
                                <p class="text-orange-600 font-semibold">{{ $dk['critical'] }} critical</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email…"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-56">
        <select name="department"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            <option value="">All Departments</option>
            @foreach ($departments as $dept)
                <option value="{{ $dept->id }}" @selected($departmentId == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Filter
        </button>
        @if ($search || $departmentId)
            <a href="{{ route('admin.staff.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium self-center">Clear</a>
        @endif
    </form>

    {{-- ── Staff performance table ──────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($staff->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-sm">No staff found.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-5 py-3 text-left">Rank</th>
                            <th class="px-5 py-3 text-left">Staff Member</th>
                            <th class="px-5 py-3 text-left">Department</th>
                            <th class="px-5 py-3 text-left">Level</th>
                            <th class="px-5 py-3 text-left">Personal Rate</th>
                            <th class="px-5 py-3 text-left">SLA Compliance</th>
                            <th class="px-5 py-3 text-left">Avg. Completion</th>
                            <th class="px-5 py-3 text-left">Overdue</th>
                            <th class="px-5 py-3 text-left">Dept. Readiness</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        {{-- Sort by personal_rate descending for ranking --}}
                        @php
                            $ranked = $performance->sortByDesc(fn ($p) => $p['personal_rate'] ?? -1)->values();
                        @endphp
                        @foreach ($ranked as $rank => $p)
                            @php
                                $dc = \App\Models\Department::ragClasses($p['dept_rate']);
                                $pc = $p['personal_rate'] !== null ? \App\Models\Department::ragClasses($p['personal_rate']) : null;
                                $rankNum = $rank + 1;
                                $medalClass = match($rankNum) {
                                    1 => 'bg-yellow-100 text-yellow-700',
                                    2 => 'bg-gray-100 text-gray-600',
                                    3 => 'bg-amber-100 text-amber-700',
                                    default => 'bg-gray-50 text-gray-400',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">

                                {{-- Rank --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $medalClass }}">
                                        {{ $rankNum <= 3 ? ['🥇','🥈','🥉'][$rankNum - 1] : '#'.$rankNum }}
                                    </span>
                                </td>

                                {{-- Name --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs shrink-0">
                                            {{ mb_strtoupper(mb_substr($p['user']->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $p['user']->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $p['user']->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Department --}}
                                <td class="px-5 py-4">
                                    @if ($p['user']->department)
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $p['user']->department->color }}"></div>
                                            <span class="text-gray-700 text-xs">{{ $p['user']->department->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Level --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $p['user']->isHod() ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $p['user']->isHod() ? 'HOD' : 'Staff' }}
                                    </span>
                                </td>

                                {{-- Personal completion rate --}}
                                <td class="px-5 py-4">
                                    @if ($pc !== null)
                                        <div class="flex items-center gap-2">
                                            <div class="w-20 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                                <div class="{{ $pc['bar'] }} h-1.5 rounded-full" style="width: {{ $p['personal_rate'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold {{ $pc['text'] }}">{{ $p['personal_rate'] }}%</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $p['assigned_done'] }}/{{ $p['assigned'] }} tasks</p>
                                    @else
                                        <span class="text-xs text-gray-400">No tasks assigned</span>
                                    @endif
                                </td>

                                {{-- SLA compliance --}}
                                <td class="px-5 py-4">
                                    @if ($p['sla_rate'] !== null)
                                        @php $slaColor = $p['sla_rate'] >= 80 ? 'text-green-700' : ($p['sla_rate'] >= 50 ? 'text-amber-700' : 'text-red-600'); @endphp
                                        <span class="text-sm font-bold {{ $slaColor }}">{{ $p['sla_rate'] }}%</span>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $p['sla_compliant'] }} on time · {{ $p['sla_missed'] }} late</p>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Avg completion time --}}
                                <td class="px-5 py-4">
                                    @if ($p['avg_completion_days'] !== null)
                                        @php $timeColor = $p['avg_completion_days'] <= 1 ? 'text-green-700' : ($p['avg_completion_days'] <= 3 ? 'text-amber-700' : 'text-red-600'); @endphp
                                        <span class="text-sm font-bold {{ $timeColor }}">{{ $p['avg_completion_days'] }}d</span>
                                        <p class="text-xs text-gray-400 mt-0.5">after deadline</p>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Overdue --}}
                                <td class="px-5 py-4">
                                    @if ($p['overdue_count'] > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                            {{ $p['overdue_count'] }} overdue
                                        </span>
                                    @else
                                        <span class="text-xs text-green-600 font-medium">✓ None</span>
                                    @endif
                                </td>

                                {{-- Dept readiness --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="{{ $dc['bar'] }} h-1.5 rounded-full" style="width: {{ $p['dept_rate'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold {{ $dc['text'] }}">{{ $p['dept_rate'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($staff->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $staff->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.admin>
