<x-layouts.admin title="Booking Details">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('admin.bookings.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Bookings
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Booking Details</h1>
                    <p class="text-gray-600 mt-1">Reference: <span class="font-mono font-semibold">{{ $booking->booking_reference }}</span></p>
                </div>
                <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full {{ $booking->getStatusBadgeClass() }}">
                    {{ $booking->getStatusLabel() }}
                </span>
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

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Event Information --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Event Information</h2>
                    
                    <dl class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Event Title</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->event_title }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Event Category</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $booking->event_category)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Event Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $booking->event_type)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Event Visibility</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $booking->event_visibility)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Expected Participants</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->expected_participants }} people</dd>
                        </div>
                        @if($booking->number_of_facilitators)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Facilitators/Speakers</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->number_of_facilitators }}</dd>
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Event Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $booking->event_description }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Purpose of Event</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $booking->event_purpose }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex gap-4">
                        @if($booking->is_residential)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Residential Event
                            </span>
                        @endif
                        @if($booking->vip_attendance)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                VIP Attendance Expected
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Organization Information --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Organization & Contact</h2>
                    
                    <dl class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Organization Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->organization_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Organization Category</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $booking->organization_category)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->contact_person_name }}</dd>
                        </div>
                        @if($booking->contact_person_designation)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Designation</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->contact_person_designation }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $booking->contact_email }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $booking->contact_email }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="tel:{{ $booking->contact_phone }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $booking->contact_phone }}
                                </a>
                            </dd>
                        </div>
                        @if($booking->country)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Country</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->country }}</dd>
                            </div>
                        @endif
                        @if($booking->kra_pin)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">KRA PIN</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $booking->kra_pin }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Event Dates & Venue --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Dates & Venue</h2>
                    
                    <dl class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Preferred Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->preferred_start_date->format('l, M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Preferred End Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->preferred_end_date->format('l, M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Daily Start Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ date('g:i A', strtotime($booking->daily_start_time)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Daily End Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ date('g:i A', strtotime($booking->daily_end_time)) }}</dd>
                        </div>
                        @if($booking->preferred_venue)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Preferred Venue</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->preferred_venue }}</dd>
                            </div>
                        @endif
                        @if($booking->allocated_venue)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Allocated Venue</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold text-green-600">{{ $booking->allocated_venue }}</dd>
                            </div>
                        @endif
                        @if($booking->seating_arrangement)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Seating Arrangement</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $booking->seating_arrangement)) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Requirements --}}
                @if($booking->accommodation_required || $booking->catering_required || $booking->equipment_requirements)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Requirements</h2>
                        
                        {{-- Accommodation --}}
                        @if($booking->accommodation_required)
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-900 mb-2">Accommodation</h3>
                                <p class="text-sm text-gray-600">
                                    <strong>Rooms:</strong> {{ $booking->accommodation_rooms }} | 
                                    <strong>Nights:</strong> {{ $booking->accommodation_nights }}
                                </p>
                            </div>
                        @endif

                        {{-- Catering --}}
                        @if($booking->catering_required)
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-900 mb-2">Catering</h3>
                                @if($booking->catering_package)
                                    <p class="text-sm text-gray-600 mb-1"><strong>Package:</strong> {{ ucfirst(str_replace('_', ' ', $booking->catering_package)) }}</p>
                                @endif
                                @if($booking->catering_details)
                                    <div class="text-sm text-gray-600">
                                        @foreach($booking->catering_details as $key => $value)
                                            @if($value)
                                                <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs mr-2 mb-1">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Equipment --}}
                        @if($booking->equipment_requirements)
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Equipment & Technical</h3>
                                <div class="text-sm text-gray-600">
                                    @foreach($booking->equipment_requirements as $key => $value)
                                        @if($value)
                                            <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs mr-2 mb-1">
                                                {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                @if(is_numeric($value) && $value > 1)
                                                    : {{ $value }}
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Linked Event --}}
                @if($booking->event_id)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-900 mb-2">Linked Internal Event</h3>
                        <p class="text-sm text-green-800">
                            This booking has been converted to an internal event.
                            <a href="{{ route('events.show', $booking->event_id) }}" class="font-semibold underline hover:text-green-900">
                                View Event →
                            </a>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Sidebar Actions --}}
            <div class="space-y-6">
                
                {{-- Quick Actions --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                    
                    <div class="space-y-3">
                        @if($booking->status === 'pending')
                            <form method="POST" action="{{ route('admin.bookings.review', $booking) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    Mark Under Review
                                </button>
                            </form>
                        @endif

                        @if(in_array($booking->status, ['pending', 'under_review']))
                            <button onclick="openApproveModal()" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors font-medium">
                                Approve Booking
                            </button>

                            <button onclick="openRejectModal()" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                                Reject Booking
                            </button>

                            <button onclick="openQuotationModal()" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                Generate Quotation
                            </button>
                        @endif

                        @if($booking->status === 'approved' && !$booking->event_id)
                            <a href="{{ route('admin.bookings.review-requirements', $booking) }}" class="w-full block text-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                Convert to Event
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Timeline</h3>
                    
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                @if($booking->reviewed_at || $booking->quotation_sent_at)
                                    <div class="w-0.5 h-8 bg-gray-200"></div>
                                @endif
                            </div>
                            <div class="flex-1 pb-4">
                                <p class="text-sm font-medium text-gray-900">Booking Submitted</p>
                                <p class="text-xs text-gray-500">{{ $booking->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>

                        @if($booking->quotation_sent_at)
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    @if($booking->reviewed_at)
                                        <div class="w-0.5 h-8 bg-gray-200"></div>
                                    @endif
                                </div>
                                <div class="flex-1 pb-4">
                                    <p class="text-sm font-medium text-gray-900">Quotation Sent</p>
                                    <p class="text-xs text-gray-500">{{ $booking->quotation_sent_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($booking->reviewed_at)
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full {{ $booking->status === 'approved' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                        @if($booking->status === 'approved')
                                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->status === 'approved' ? 'Approved' : 'Rejected' }}</p>
                                    <p class="text-xs text-gray-500">{{ $booking->reviewed_at->format('M d, Y g:i A') }}</p>
                                    @if($booking->reviewedBy)
                                        <p class="text-xs text-gray-500">by {{ $booking->reviewedBy->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quotation Info --}}
                @if($booking->estimated_cost)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Quotation</h3>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Estimated Cost:</span>
                                <span class="font-semibold text-gray-900">KES {{ number_format($booking->estimated_cost, 2) }}</span>
                            </div>
                            @if($booking->deposit_amount)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Deposit Required:</span>
                                    <span class="font-semibold text-gray-900">KES {{ number_format($booking->deposit_amount, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div id="approveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Booking</h3>
            <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                @csrf
                <div class="mb-4">
                    <label for="allocated_venue" class="block text-sm font-medium text-gray-700 mb-1">Allocate Venue *</label>
                    <input 
                        type="text" 
                        id="allocated_venue" 
                        name="allocated_venue" 
                        list="venues" 
                        required
                        placeholder="Type or select a venue..."
                        value="{{ $booking->preferred_venue }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <datalist id="venues">
                        <option value="Main Conference Hall">
                        <option value="Training Room A">
                        <option value="Training Room B">
                        <option value="Training Room C">
                        <option value="Boardroom">
                        <option value="Outdoor Pavilion">
                        <option value="Auditorium">
                        <option value="Executive Lounge">
                        <option value="Multi-Purpose Hall">
                        <option value="Library Conference Room">
                    </datalist>
                    <p class="text-xs text-gray-500 mt-1">Client preference: {{ $booking->preferred_venue ?? 'Not specified' }}</p>
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeApproveModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Booking</h3>
            <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection *</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quotation Modal --}}
    <div id="quotationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Quotation</h3>
            <form method="POST" action="{{ route('admin.bookings.quotation', $booking) }}">
                @csrf
                <div class="space-y-4 mb-4">
                    <div>
                        <label for="estimated_cost" class="block text-sm font-medium text-gray-700 mb-1">Total Estimated Cost (KES) *</label>
                        <input type="number" id="estimated_cost" name="estimated_cost" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label for="deposit_amount" class="block text-sm font-medium text-gray-700 mb-1">Deposit Amount (KES)</label>
                        <input type="number" id="deposit_amount" name="deposit_amount" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label for="quotation_breakdown" class="block text-sm font-medium text-gray-700 mb-1">Cost Breakdown *</label>
                        <textarea id="quotation_breakdown" name="quotation_breakdown" rows="6" required placeholder="Venue hire: KES XX,XXX&#10;Catering (XX pax): KES XX,XXX&#10;Equipment: KES XX,XXX&#10;etc..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeQuotationModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Send Quotation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApproveModal() {
            document.getElementById('approveModal').classList.remove('hidden');
        }
        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }
        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
        function openQuotationModal() {
            document.getElementById('quotationModal').classList.remove('hidden');
        }
        function closeQuotationModal() {
            document.getElementById('quotationModal').classList.add('hidden');
        }
    </script>

</x-layouts.admin>
