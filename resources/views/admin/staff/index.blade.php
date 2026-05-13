<x-layouts.admin>
    <x-slot:title>Staff Performance</x-slot:title>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
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

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($staff->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-sm">No staff found.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-left">Staff Member</th>
                        <th class="px-5 py-3 text-left">Department</th>
                        <th class="px-5 py-3 text-left">Level</th>
                        <th class="px-5 py-3 text-left">Dept. Readiness</th>
                        <th class="px-5 py-3 text-left">Personal Tasks</th>
                        <th class="px-5 py-3 text-left">Personal Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($performance as $p)
                        @php
                            $dc = \App\Models\Department::ragClasses($p['dept_rate']);
                            $pc = $p['personal_rate'] !== null ? \App\Models\Department::ragClasses($p['personal_rate']) : null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
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
                            <td class="px-5 py-4">
                                @if ($p['user']->department)
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2 h-2 rounded-full" style="background-color: {{ $p['user']->department->color }}"></div>
                                        <span class="text-gray-700">{{ $p['user']->department->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $p['user']->isHod() ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $p['user']->isHod() ? 'HOD' : 'Staff' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="{{ $dc['bar'] }} h-1.5 rounded-full" style="width: {{ $p['dept_rate'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold {{ $dc['text'] }}">{{ $p['dept_rate'] }}%</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $p['dept_done'] }}/{{ $p['dept_total'] }} tasks</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600 text-xs">
                                @if ($p['assigned'] > 0)
                                    {{ $p['assigned_done'] }}/{{ $p['assigned'] }} assigned
                                @else
                                    <span class="text-gray-400">No assigned tasks</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($pc !== null)
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="{{ $pc['bar'] }} h-1.5 rounded-full" style="width: {{ $p['personal_rate'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold {{ $pc['text'] }}">{{ $p['personal_rate'] }}%</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($staff->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $staff->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.admin>
