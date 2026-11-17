<x-layouts.admin>
    <x-slot:title>{{ $financialYear->name }}</x-slot:title>

    <div class="max-w-6xl">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $financialYear->name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $financialYear->start_date->format('d M Y') }} – {{ $financialYear->end_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (!$financialYear->is_current && !$financialYear->is_locked)
                    <form method="POST" action="{{ route('admin.financial-years.set-current', $financialYear) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-1.5 border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            Set as Current
                        </button>
                    </form>
                @endif
                @if (!$financialYear->is_locked)
                    <a href="{{ route('admin.financial-years.edit', $financialYear) }}"
                        class="inline-flex items-center gap-1.5 border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Edit
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Status Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Status</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        @if ($financialYear->is_current)
                            <span class="px-2.5 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Current FY</span>
                        @else
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded">Not Current</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($financialYear->is_locked)
                            <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">Locked</span>
                        @else
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Active</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Planning Weeks Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Planning Weeks</h3>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $financialYear->planning_weeks_count }}</div>
                <div class="space-y-1 text-sm">
                    @foreach ($weeksByCampus as $campus => $count)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $campus }}</span>
                            <span class="font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Transition Weeks --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Transition Weeks</h3>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $transitionWeeks->count() }}</div>
                <p class="text-xs text-gray-500">Weeks spanning FY boundary</p>
            </div>
        </div>

        {{-- Generate Weeks Section --}}
        @if (!$financialYear->is_locked)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
                <h3 class="font-semibold text-gray-900 mb-4">Generate Planning Weeks</h3>
                <form method="POST" action="{{ route('admin.financial-years.generate-weeks', $financialYear) }}" class="flex items-end gap-3">
                    @csrf
                    <div>
                        <label for="campus" class="block text-sm font-medium text-gray-700 mb-1">Campus</label>
                        <select name="campus" id="campus" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="NRB">Nairobi (NRB)</option>
                            <option value="MTB">Mombasa (MTB)</option>
                            <option value="BRC">Baringo (BRC)</option>
                            <option value="LWR">Lower Kabete (LWR)</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="force" id="force" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="force" class="text-sm text-gray-700">Regenerate if exists</label>
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-4 py-2 rounded-lg">
                        Generate Weeks
                    </button>
                </form>
            </div>
        @endif

        {{-- Planning Weeks List --}}
        @if ($financialYear->planningWeeks->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">Planning Weeks</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Week</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campus</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Events</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($financialYear->planningWeeks->sortBy('week_start') as $week)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('admin.planning-weeks.show', $week) }}" class="font-medium text-indigo-600 hover:text-indigo-700">
                                            {{ $week->label }}
                                        </a>
                                        <p class="text-xs text-gray-500">Week {{ $week->week_number }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 font-mono text-xs">{{ $week->schedule_reference ?? 'N/A' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $week->campus ?? 'N/A' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $week->events_count }}</td>
                                    <td class="px-5 py-3">
                                        @if ($week->is_current)
                                            <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded">Current</span>
                                        @elseif ($week->is_transition_week)
                                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-medium rounded">Transition</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Close/Reopen Section --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Financial Year Management</h3>
            
            @if ($financialYear->is_locked)
                <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-800 font-medium mb-2">This financial year is locked</p>
                    @if ($financialYear->closedBy)
                        <p class="text-xs text-red-700">
                            Closed by {{ $financialYear->closedBy->name }} on {{ $financialYear->closed_at->format('d M Y, H:i') }}
                        </p>
                    @endif
                    @if ($financialYear->closure_notes)
                        <p class="text-xs text-red-700 mt-1">Notes: {{ $financialYear->closure_notes }}</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.financial-years.reopen', $financialYear) }}">
                    @csrf
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium text-sm px-4 py-2 rounded-lg">
                        Reopen Financial Year
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.financial-years.close', $financialYear) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="closure_notes" class="block text-sm font-medium text-gray-700 mb-1">Closure Notes (optional)</label>
                        <textarea name="closure_notes" id="closure_notes" rows="3"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="Reason for closing this financial year..."></textarea>
                    </div>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium text-sm px-4 py-2 rounded-lg"
                        onclick="return confirm('Are you sure you want to close this financial year? This will lock all records.')">
                        Close & Lock Financial Year
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.admin>
