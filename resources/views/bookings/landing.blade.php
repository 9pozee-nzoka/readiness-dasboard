<x-layouts.public title="Book an Event - KSG">

    {{-- Hero Section with Split Layout --}}
    <div class="bg-gradient-to-br from-green-800 via-green-700 to-green-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="grid lg:grid-cols-2 gap-6 lg:gap-8 items-center">
                {{-- Left Side - Image --}}
                <div class="order-2 lg:order-1">
                    <img src="{{ asset('assets/images/banner/Events and Venues Showcase.png') }}" 
                         alt="KSG Events and Venues" 
                         class="w-full h-auto rounded-xl shadow-2xl border-4 border-yellow-500">
                </div>

                {{-- Right Side - Content --}}
                <div class="order-1 lg:order-2">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4 leading-tight text-yellow-400">
                        Book Your Event at Kenya School of Government
                    </h1>
                    <p class="text-base sm:text-lg text-white mb-6 leading-relaxed">
                        Host your conference, workshop, training, or seminar at our world-class facilities. 
                        We provide comprehensive event management services tailored to your needs.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 mb-6">
                        <a href="{{ route('bookings.create') }}" 
                           class="bg-yellow-500 text-green-900 px-6 py-3 rounded-lg hover:bg-yellow-400 hover:shadow-xl transition-all font-bold text-base shadow-lg text-center transform hover:scale-105">
                            Start Booking Now
                        </a>
                        <a href="#track-booking" 
                           class="bg-white text-green-800 border-2 border-yellow-500 px-6 py-3 rounded-lg hover:bg-green-50 transition-all font-semibold text-base text-center">
                            Track My Booking
                        </a>
                    </div>

                    <div class="flex gap-3 text-sm">
                        <a href="{{ route('login') }}" class="text-yellow-400 hover:text-yellow-300 font-medium transition-colors">
                            Login
                        </a>
                        <span class="text-yellow-500">|</span>
                        <a href="{{ route('register.client.form') }}" class="text-yellow-400 hover:text-yellow-300 font-medium transition-colors">
                            Sign Up
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Features Section --}}
    <div class="bg-white py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-green-900 mb-2">What We Offer</h2>
                <p class="text-base text-gray-600 max-w-2xl mx-auto">
                    Experience exceptional service and world-class amenities for your next event
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                {{-- Feature 1 - Modern Facilities --}}
                <div class="flex gap-3 items-start bg-green-50 p-4 rounded-lg border-l-4 border-green-700 hover:shadow-lg transition-shadow">
                    <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden shadow-md border-2 border-yellow-500">
                        <img src="{{ asset('assets/images/context/Modern Conference Hall Setup.png') }}" 
                             alt="Modern Conference Hall" 
                             class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold mb-1 text-green-900">Modern Facilities</h3>
                        <p class="text-gray-700 text-sm leading-relaxed">State-of-the-art conference halls, meeting rooms, and training centers equipped with the latest technology.</p>
                    </div>
                </div>

                {{-- Feature 2 - Catering Services --}}
                <div class="flex gap-3 items-start bg-green-50 p-4 rounded-lg border-l-4 border-green-700 hover:shadow-lg transition-shadow">
                    <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden shadow-md border-2 border-yellow-500">
                        <img src="{{ asset('assets/images/context/Elegant Catering Buffet Service.png') }}" 
                             alt="Catering Buffet Service" 
                             class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold mb-1 text-green-900">Catering Services</h3>
                        <p class="text-gray-700 text-sm leading-relaxed">Professional catering with diverse menu options, from basic packages to VIP dining experiences.</p>
                    </div>
                </div>

                {{-- Feature 3 - Full Support --}}
                <div class="flex gap-3 items-start bg-green-50 p-4 rounded-lg border-l-4 border-green-700 hover:shadow-lg transition-shadow">
                    <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden shadow-md border-2 border-yellow-500">
                        <img src="{{ asset('assets/images/context/Elegant Blue and White Ballroom Banquet.png') }}" 
                             alt="Ballroom Banquet Setup" 
                             class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold mb-1 text-green-900">Full Support</h3>
                        <p class="text-gray-700 text-sm leading-relaxed">Dedicated event coordination, technical support, and accommodation arrangements for your convenience.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Event Types Section --}}
    <div class="bg-gradient-to-br from-green-50 to-yellow-50 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-green-900 mb-2">Ideal for Various Events</h2>
                <p class="text-base text-gray-700 max-w-2xl mx-auto">
                    Whatever your event needs, we have the perfect space and services
                </p>
            </div>
            
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="group bg-white p-5 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 text-center border-t-4 border-green-700">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-600 to-green-800 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base mb-1 text-green-900">Conferences</h3>
                    <p class="text-gray-600 text-xs">Large-scale events with breakout rooms</p>
                </div>
                <div class="group bg-white p-5 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 text-center border-t-4 border-green-700">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-600 to-green-800 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base mb-1 text-green-900">Workshops</h3>
                    <p class="text-gray-600 text-xs">Interactive training sessions</p>
                </div>
                <div class="group bg-white p-5 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 text-center border-t-4 border-green-700">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-600 to-green-800 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base mb-1 text-green-900">Seminars</h3>
                    <p class="text-gray-600 text-xs">Professional development programs</p>
                </div>
                <div class="group bg-white p-5 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 text-center border-t-4 border-green-700">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-600 to-green-800 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base mb-1 text-green-900">Retreats</h3>
                    <p class="text-gray-600 text-xs">Team building and strategic planning</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Track Booking Section --}}
    <div id="track-booking" class="bg-white py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-green-50 to-yellow-50 p-6 rounded-xl shadow-xl border-2 border-green-200">
                <h2 class="text-xl font-bold mb-3 text-green-900 text-center">Track Your Booking</h2>
                <p class="text-gray-700 mb-4 text-center text-sm">Enter your booking reference and email to check the status of your booking.</p>
                
                @if (session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded mb-4 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('bookings.track') }}" class="space-y-3">
                    @csrf
                    
                    <div>
                        <label for="booking_reference" class="block text-sm font-medium text-green-900 mb-1">
                            Booking Reference
                        </label>
                        <input type="text" 
                               id="booking_reference" 
                               name="booking_reference" 
                               placeholder="e.g., KSG-BK-2611-0001"
                               required
                               class="w-full px-3 py-2 border-2 border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-green-900 mb-1">
                            Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               placeholder="your@email.com"
                               required
                               class="w-full px-3 py-2 border-2 border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    </div>

                    <button type="submit" 
                            class="w-full bg-green-700 text-white px-6 py-2.5 rounded-lg hover:bg-green-800 transition-colors font-semibold text-sm shadow-md">
                        Track Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- CTA Section --}}
    <div class="bg-gradient-to-r from-green-800 to-green-700 py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold mb-2 text-yellow-400">Ready to Book Your Event?</h2>
            <p class="text-lg text-yellow-100 mb-6 font-medium">Our team is here to help you create a successful event.</p>
            <a href="{{ route('bookings.create') }}" 
               class="inline-block bg-yellow-500 text-green-900 px-8 py-3 rounded-lg hover:bg-yellow-400 transition-colors font-bold text-base shadow-lg">
                Start Your Booking Request
            </a>
        </div>
    </div>

</x-layouts.public>
