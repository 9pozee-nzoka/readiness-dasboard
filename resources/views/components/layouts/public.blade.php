<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'KSG Event Booking' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen">

    {{-- Navigation --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                {{-- Brand --}}
                <a href="{{ route('bookings.landing') }}" class="flex items-center gap-3">
                    <div class="flex items-center">
                        <img src="{{ asset('assets/logos/1.png') }}" alt="" class="h-8 w-auto object-contain">
                        <img src="{{ asset('assets/logos/2.png') }}" alt="" class="h-8 w-auto object-contain">
                        <img src="{{ asset('assets/logos/3.png') }}" alt="" class="h-8 w-auto object-contain">
                    </div>
                    <span class="font-semibold text-gray-900 text-lg hidden sm:block">KSG Events</span>
                </a>

                {{-- Right side --}}
                <div class="flex items-center gap-4">
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium">Login</a>
                        <a href="{{ route('register.client.form') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium">Sign Up</a>
                    @endguest
                    
                    @auth
                        @if(auth()->user()->isClient())
                            <a href="{{ route('bookings.my-bookings') }}" class="text-gray-600 hover:text-gray-900 font-medium">My Bookings</a>
                        @else
                            <a href="{{ route('dashboard.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 font-medium">Logout</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Main content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600 text-sm">
                <p>&copy; {{ date('Y') }} Kenya School of Government. All rights reserved.</p>
                <p class="mt-2">For inquiries, contact us at <a href="mailto:events@ksg.ac.ke" class="text-indigo-600 hover:text-indigo-800">events@ksg.ac.ke</a></p>
            </div>
        </div>
    </footer>

</body>
</html>
