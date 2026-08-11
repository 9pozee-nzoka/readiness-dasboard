<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Event Readiness Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo / Brand --}}
        <div class="flex justify-center mb-8">
            <div class="flex items-center">
                <img src="{{ asset('assets/logos/1.png') }}" alt="Logo 1" class="h-14 w-auto object-contain">
                <img src="{{ asset('assets/logos/2.png') }}" alt="Logo 2" class="h-14 w-auto object-contain">
                <img src="{{ asset('assets/logos/3.png') }}" alt="Logo 3" class="h-14 w-auto object-contain">
            </div>
        </div>
        <div class="text-center mb-8 -mt-4">
            <h1 class="text-2xl font-bold text-gray-900">Event Readiness Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        autofocus
                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}"
                        placeholder="you@example.com">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            name="password"
                            id="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="block w-full rounded-lg border px-3.5 py-2.5 pr-11 text-sm shadow-sm transition
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 transition-colors"
                            :aria-label="show ? 'Hide password' : 'Show password'">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" id="remember"
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors shadow-sm">
                    Sign in
                </button>
            </form>
        </div>

        {{-- Link to register --}}
        <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Create one</a>
        </p>

        {{-- Demo credentials hint --}}
        <div class="mt-6 bg-white rounded-xl border border-gray-200 p-4 text-xs text-gray-500">
            <p class="font-semibold text-gray-700 mb-2">Demo accounts</p>
            <p>Contact your administrator for demo credentials.</p>
        </div>
    </div>

</body>
</html>
