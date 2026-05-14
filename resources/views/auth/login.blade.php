<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Event Readiness Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <img src="{{ asset('assets/logos/3.png') }}" alt="Logo" class="w-14 h-14 rounded-2xl mb-4 shadow-lg object-contain mx-auto">
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
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        autocomplete="current-password"
                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}"
                        placeholder="••••••••">
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
