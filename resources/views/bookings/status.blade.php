<x-layouts.public title="Booking Status">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div class="mb-8">
            <a href="{{ route('bookings.landing') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Home
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Booking Status</h1>
        </div>

        {{-- Booking Overview --}}
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6 sm:p-8 mb-6">
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-1">{{ $booking->event_title }}</h2>
                    <p class="text-gray-600">Booking Reference: <span class="font-mono font-semibold">{{ $booking->booking_reference }}</span></p>
                </div>
                <span class="inline-flex px-4 py-2 rounded-full text-sm font-semibold {{ $booking->getStatusBadgeClass() }}">
                    {{ $booking->getStatusLabel() }}
                </span>
            </div>

            {{-- Status Timeline --}}
            <div class="mb-8">
                <h3 class="font-semibold text-gray-900 mb-4">Booking Progress</h3>
                <div class="space-y-4">
                    {{-- Submitted --}}
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="w-0.5 h-12 bg-gray-200"></div>
                        </div>
                        <div class="flex-1 pb-8">
                            <h4 class="font-semibold text-gray-900">Request Submitted</h4>
                            <p class="text-sm text-gray-600">{{ $booking->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>

                    {{-- Under Review --}}
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full {{ in_array($booking->status, ['under_review', 'approved', 'confirmed']) ? 'bg-blue-100' : 'bg-gray-100' }} flex items-center justify-center">
                                @if(in_array($booking->status, ['under_review', 'approved', 'confirmed']))
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                                @endif
                            </div>
                            <div class="w-0.5 h-12 bg-gray-200"></div>
                        </div>
                        <div class="flex-1 pb-8">
                            <h4 class="font-semibold text-gray-900">Under Review</h4>
                            <p class="text-sm text-gray-600">
                                @if($booking->status === 'pending')
                                    Pending review by our team
                                @else
                                    Being processed
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Quotation --}}
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full {{ $booking->quotation_sent_at ? 'bg-purple-100' : 'bg-gray-100' }} flex items-center justify-center">
                                @if($booking->quotation_sent_at)
                                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                                @endif
                            </div>
                            <div class="w-0.5 h-12 bg-gray-200"></div>
                        </div>
                        <div class="flex-1 pb-8">
                            <h4 class="font-semibold text-gray-900">Quotation</h4>
                            <p class="text-sm text-gray-600">
                                @if($booking->quotation_sent_at)
                                    Sent on {{ $booking->quotation_sent_at->format('M d, Y') }}
                                @else
                                    Quotation will be prepared after review
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Final Status --}}
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full {{ $booking->status === 'approved' || $booking->status === 'confirmed' ? 'bg-green-100' : ($booking->status === 'rejected' ? 'bg-red-100' : 'bg-gray-100') }} flex items-center justify-center">
                                @if($booking->status === 'approved' || $booking->status === 'confirmed')
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($booking->status === 'rejected')
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">
                                @if($booking->status === 'approved')
                                    Approved
                                @elseif($booking->status === 'confirmed')
                                    Confirmed
                                @elseif($booking->status === 'rejected')
                                    Declined
                                @else
                                    Final Decision
                                @endif
                            </h4>
                            <p class="text-sm text-gray-600">
                                @if($booking->reviewed_at)
                                    {{ $booking->reviewed_at->format('M d, Y') }}
                                @else
                                    Awaiting decision
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Event Details --}}
            <div class="border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900 mb-4">Event Details</h3>
                <dl class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-600">Organization</dt>
                        <dd class="font-semibold text-gray-900">{{ $booking->organization_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Event Category</dt>
                        <dd class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $booking->event_category)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Event Dates</dt>
                        <dd class="font-semibold text-gray-900">
                            {{ $booking->preferred_start_date->format('M d, Y') }} - {{ $booking->preferred_end_date->format('M d, Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Participants</dt>
                        <dd class="font-semibold text-gray-900">{{ $booking->expected_participants }} people</dd>
                    </div>
                    @if($booking->preferred_venue)
                        <div>
                            <dt class="text-sm text-gray-600">Preferred Venue</dt>
                            <dd class="font-semibold text-gray-900">{{ $booking->preferred_venue }}</dd>
                        </div>
                    @endif
                    @if($booking->estimated_cost)
                        <div>
                            <dt class="text-sm text-gray-600">Estimated Cost</dt>
                            <dd class="font-semibold text-gray-900">KES {{ number_format($booking->estimated_cost, 2) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Rejection Reason --}}
            @if($booking->status === 'rejected' && $booking->rejection_reason)
                <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-semibold text-red-900 mb-2">Reason for Declining</h4>
                    <p class="text-sm text-red-800">{{ $booking->rejection_reason }}</p>
                </div>
            @endif

            {{-- Review Notes --}}
            @if($booking->review_notes)
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">Notes from Our Team</h4>
                    <p class="text-sm text-blue-800">{{ $booking->review_notes }}</p>
                </div>
            @endif
        </div>

        {{-- Contact Section --}}
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <h3 class="font-semibold text-gray-900 mb-2">Need Assistance?</h3>
            <p class="text-gray-600 mb-4">Our events team is here to help</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="mailto:events@ksg.ac.ke" class="inline-flex items-center justify-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    events@ksg.ac.ke
                </a>
                <span class="text-gray-400 hidden sm:inline">|</span>
                <a href="tel:+254202717260" class="inline-flex items-center justify-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    +254 20 2717260
                </a>
            </div>
        </div>
    </div>

</x-layouts.public>
