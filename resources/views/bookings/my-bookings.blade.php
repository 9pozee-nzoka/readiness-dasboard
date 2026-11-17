<x-layouts.public title="My Bookings">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Bookings</h1>
                    <p class="text-gray-600 mt-1">View and track all your event booking requests</p>
                </div>
                <a href="{{ route('bookings.create') }}" 
                   class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                    New Booking
                </a>
            </div>
        </div>

        @if($bookings->count() > 0)
            <div class="space-y-4">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-lg shadow border border-gray-200 hover:border-indigo-300 transition-colors">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $booking->event_title }}</h3>
                                    <p class="text-sm text-gray-600">{{ $booking->organization_name }}</p>
                                </div>
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $booking->getStatusBadgeClass() }}">
                                    {{ $booking->getStatusLabel() }}
                                </span>
                            </div>

                            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-gray-500">Booking Reference</p>
                                    <p class="text-sm font-mono font-semibold text-gray-900">{{ $booking->booking_reference }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Event Dates</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $booking->preferred_start_date->format('M d, Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Participants</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $booking->expected_participants }} people</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Submitted</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $booking->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            @if($booking->estimated_cost)
                                <div class="bg-purple-50 border border-purple-200 rounded p-3 mb-4">
                                    <p class="text-xs text-purple-600 font-medium">Estimated Cost</p>
                                    <p class="text-lg font-bold text-purple-900">KES {{ number_format($booking->estimated_cost, 2) }}</p>
                                    @if($booking->deposit_amount)
                                        <p class="text-xs text-purple-700">Deposit: KES {{ number_format($booking->deposit_amount, 2) }}</p>
                                    @endif
                                </div>
                            @endif

                            @if($booking->status === 'rejected' && $booking->rejection_reason)
                                <div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
                                    <p class="text-xs text-red-600 font-medium mb-1">Reason for Declining</p>
                                    <p class="text-sm text-red-800">{{ $booking->rejection_reason }}</p>
                                </div>
                            @endif

                            @if($booking->review_notes)
                                <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                                    <p class="text-xs text-blue-600 font-medium mb-1">Notes from Our Team</p>
                                    <p class="text-sm text-blue-800">{{ $booking->review_notes }}</p>
                                </div>
                            @endif

                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    @if($booking->reviewed_at)
                                        Reviewed on {{ $booking->reviewed_at->format('M d, Y') }}
                                    @else
                                        Pending review
                                    @endif
                                </div>
                                <a href="{{ route('bookings.track') }}?booking_reference={{ $booking->booking_reference }}&email={{ $booking->contact_email }}" 
                                   onclick="event.preventDefault(); document.getElementById('track-{{ $booking->id }}').submit();"
                                   class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                    View Full Details →
                                </a>
                                <form id="track-{{ $booking->id }}" method="POST" action="{{ route('bookings.track') }}" class="hidden">
                                    @csrf
                                    <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                                    <input type="hidden" name="email" value="{{ $booking->contact_email }}">
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No bookings yet</h3>
                <p class="mt-2 text-gray-600">You haven't submitted any event booking requests.</p>
                <a href="{{ route('bookings.create') }}" 
                   class="mt-6 inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                    Submit Your First Booking
                </a>
            </div>
        @endif
    </div>

</x-layouts.public>
