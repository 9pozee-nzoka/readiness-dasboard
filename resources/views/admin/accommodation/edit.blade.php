<x-layouts.admin>
    <x-slot:title>Edit Accommodation - {{ $planningWeek->label }}</x-slot:title>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Accommodation Management</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $planningWeek->label }} ({{ $planningWeek->week_start->format('d M') }} – {{ $planningWeek->week_end->format('d M Y') }})</p>
        </div>
        <div class="flex gap-2">
            @if($previousData->isNotEmpty())
                <form method="POST" action="{{ route('admin.accommodation.prefill', $planningWeek) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 border border-indigo-300 bg-indigo-50 text-indigo-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-indigo-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Prefill from Previous Week
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.planning-weeks.show', $planningWeek) }}" class="inline-flex items-center border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg hover:border-gray-400">
                Back to Schedule
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.accommodation.update', $planningWeek) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hostel Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Residency</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Occupancy</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Out of Order</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($hostels as $index => $hostel)
                        @php
                            $current = $accommodation->get($hostel->id);
                            $previous = $previousData->get($hostel->id);
                            $occupancy = $current?->occupancy ?? $previous?->occupancy ?? 0;
                            $outOfOrder = $current?->rooms_out_of_order ?? $previous?->rooms_out_of_order ?? 0;
                            $available = $hostel->availableRooms($occupancy, $outOfOrder);
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $hostel->name }}</div>
                                <input type="hidden" name="hostels[{{ $index }}][hostel_id]" value="{{ $hostel->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $hostel->residency_type === 'Executive' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $hostel->residency_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-900 font-semibold">{{ $hostel->capacity }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <input type="number" 
                                       name="hostels[{{ $index }}][occupancy]" 
                                       value="{{ old("hostels.{$index}.occupancy", $occupancy) }}"
                                       min="0" 
                                       max="{{ $hostel->capacity }}"
                                       class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                       required>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <input type="number" 
                                       name="hostels[{{ $index }}][rooms_out_of_order]" 
                                       value="{{ old("hostels.{$index}.rooms_out_of_order", $outOfOrder) }}"
                                       min="0" 
                                       max="{{ $hostel->capacity }}"
                                       class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                       required>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded text-sm font-medium {{ $available > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $available }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <input type="text" 
                                       name="hostels[{{ $index }}][notes]" 
                                       value="{{ old("hostels.{$index}.notes", $current?->notes ?? $previous?->notes) }}"
                                       placeholder="Optional notes..."
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Outsourced Accommodation</label>
            <input type="number" 
                   name="outsourced_accommodation" 
                   value="{{ old('outsourced_accommodation', $planningWeek->outsourced_accommodation ?? 0) }}"
                   min="0"
                   class="w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            <p class="mt-1 text-xs text-gray-500">Number of participants accommodated outside KSG hostels</p>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.planning-weeks.show', $planningWeek) }}" class="inline-flex items-center border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg hover:border-gray-400">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
                Save Accommodation Data
            </button>
        </div>
    </form>
</x-layouts.admin>
