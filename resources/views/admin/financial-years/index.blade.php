<x-layouts.admin>
    <x-slot:title>Financial Years</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Financial Years</h2>
            <p class="text-sm text-gray-500">Manage financial years, planning weeks, and budget carry-forwards</p>
        </div>
        <a href="{{ route('admin.financial-years.create') }}"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            + New Financial Year
        </a>
    </div>

    @if ($financialYears->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 text-center py-16 px-6">
            <p class="text-gray-600 font-medium">No financial years yet</p>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                Create a financial year to start managing planning weeks and budget tracking.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($financialYears as $fy)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg">{{ $fy->name }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ $fy->start_date->format('d M Y') }} – {{ $fy->end_date->format('d M Y') }}
                            </p>
                        </div>
                        <div class="flex gap-1">
                            @if ($fy->is_current)
                                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded">Current</span>
                            @endif
                            @if ($fy->is_locked)
                                <span class="px-2 py-0.5 bg-red-100 text-red-800 text-xs font-medium rounded">Locked</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Planning Weeks</span>
                            <span class="font-medium text-gray-900">{{ $fy->planning_weeks_count }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Status</span>
                            <span class="font-medium {{ $fy->is_locked ? 'text-red-600' : 'text-green-600' }}">
                                {{ $fy->is_locked ? 'Closed' : 'Active' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.financial-years.show', $fy) }}"
                            class="flex-1 text-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            View Details
                        </a>
                        @if (!$fy->is_locked)
                            <a href="{{ route('admin.financial-years.edit', $fy) }}"
                                class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-medium rounded-lg transition-colors">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
