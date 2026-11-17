<x-layouts.admin title="Manage Bookings">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">External Event Bookings</h1>
                <p class="text-gray-600 mt-1">Manage booking requests from external clients</p>
            </div>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px overflow-x-auto">
                    <a href="{{ route('admin.bookings.index') }}" 
                       class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ !request('status') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        All
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ !request('status') ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['all'] }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" 
                       class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ request('status') === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Pending
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ request('status') === 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['pending'] }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', ['status' => 'under_review']) }}" 
                       class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ request('status') === 'under_review' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Under Review
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ request('status') === 'under_review' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['under_review'] }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', ['status' => 'approved']) }}" 
                       class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ request('status') === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Approved
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ request('status') === 'approved' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['approved'] }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', ['status' => 'rejected']) }}" 
                       class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ request('status') === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Rejected
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ request('status') === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $statusCounts['rejected'] }}
                        </span>
                    </a>
                </nav>
            </div>
        </div>

        {{-- Success/Error Messages --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        {{-- Bookings Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($bookings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Booking Reference
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Event / Organization
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Event Dates
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Participants
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Submitted
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                            {{ $booking->booking_reference }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($booking->event_title, 40) }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->organization_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $booking->preferred_start_date->format('M d, Y') }}
                                        @if($booking->preferred_start_date->format('Y-m-d') !== $booking->preferred_end_date->format('Y-m-d'))
                                            <br>to {{ $booking->preferred_end_date->format('M d, Y') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $booking->expected_participants }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $booking->getStatusBadgeClass() }}">
                                            {{ $booking->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $booking->created_at->format('M d, Y') }}
                                        <br>
                                        <span class="text-xs">{{ $booking->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $bookings->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if(request('status'))
                            No bookings with status "{{ request('status') }}" at this time.
                        @else
                            No booking requests have been submitted yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

</x-layouts.admin>
