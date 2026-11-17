<x-layouts.public title="Booking Confirmed">

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        {{-- Success Message --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Booking Request Submitted!</h1>
            <p class="text-lg text-gray-600">Thank you for choosing Kenya School of Government</p>
        </div>

        {{-- Booking Details Card --}}
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6 sm:p-8 mb-6">
            <div class="border-b border-gray-200 pb-4 mb-4">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Booking Reference</h2>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-mono font-bold text-indigo-600">{{ $booking->booking_reference }}</span>
                    <button onclick="copyToClipboard('{{ $booking->booking_reference }}')" 
                            class="text-gray-400 hover:text-gray-600"
                            title="Copy to clipboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-2">Please save this reference number for tracking your booking.</p>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Event:</span>
                    <span class="font-semibold text-gray-900">{{ $booking->event_title }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Organization:</span>
                    <span class="font-semibold text-gray-900">{{ $booking->organization_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Event Dates:</span>
                    <span class="font-semibold text-gray-900">
                        {{ $booking->preferred_start_date->format('M d, Y') }} - {{ $booking->preferred_end_date->format('M d, Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Participants:</span>
                    <span class="font-semibold text-gray-900">{{ $booking->expected_participants }} people</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $booking->getStatusBadgeClass() }}">
                        {{ $booking->getStatusLabel() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- What's Next --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="font-semibold text-blue-900 mb-3">What happens next?</h3>
            <ol class="space-y-2 text-sm text-blue-800">
                <li class="flex gap-2">
                    <span class="font-semibold">1.</span>
                    <span>You'll receive a confirmation email at <strong>{{ $booking->contact_email }}</strong></span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">2.</span>
                    <span>Our events team will review your booking request</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">3.</span>
                    <span>We'll send you a detailed quotation within 2-3 business days</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">4.</span>
                    <span>Once approved, you'll receive booking confirmation and payment instructions</span>
                </li>
            </ol>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('bookings.landing') }}" 
               class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors font-semibold text-center">
                Return to Home
            </a>
            <a href="{{ route('bookings.landing') }}#track-booking" 
               class="flex-1 bg-white text-indigo-600 px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors font-semibold text-center border-2 border-indigo-600">
                Track My Booking
            </a>
        </div>

        {{-- Contact Info --}}
        <div class="text-center mt-8 text-gray-600 text-sm">
            <p>Questions? Contact us at <a href="mailto:events@ksg.ac.ke" class="text-indigo-600 hover:text-indigo-800 font-medium">events@ksg.ac.ke</a></p>
            <p class="mt-1">or call <a href="tel:+254202717260" class="text-indigo-600 hover:text-indigo-800 font-medium">+254 20 2717260</a></p>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Booking reference copied to clipboard!');
            });
        }
    </script>

</x-layouts.public>
