<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account — Event Readiness Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 rounded-2xl mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create your account</h1>
            <p class="text-sm text-gray-500 mt-1">Event Readiness Dashboard</p>
        </div>

        {{-- Info banner --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-6 text-xs text-blue-800 flex gap-2">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <span>Your account will be reviewed by an administrator before you can sign in. You'll be able to log in once approved.</span>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Full name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Full name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        autofocus
                        placeholder="e.g. Jane Mwangi"
                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email address <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="you@organisation.com"
                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Department --}}
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="department_id"
                        id="department_id"
                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            {{ $errors->has('department_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        <option value="">Select your department…</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Level --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Your level <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        {{-- HOD option --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" name="declared_level" value="hod"
                                class="peer sr-only"
                                {{ old('declared_level') === 'hod' ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-gray-200 p-4 text-center transition-all
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50
                                hover:border-gray-300">
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">HOD</p>
                                <p class="text-xs text-gray-500 mt-0.5">Head of Department</p>
                            </div>
                        </label>

                        {{-- Staff option --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" name="declared_level" value="employee"
                                class="peer sr-only"
                                {{ old('declared_level') === 'employee' ? 'checked' : '' }}>
                            <div class="rounded-xl border-2 border-gray-200 p-4 text-center transition-all
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50
                                hover:border-gray-300">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">Staff</p>
                                <p class="text-xs text-gray-500 mt-0.5">Department employee</p>
                            </div>
                        </label>
                    </div>
                    @error('declared_level')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                            {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Confirm password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Re-enter your password"
                        class="block w-full rounded-lg border px-3.5 py-2.5 text-sm shadow-sm transition
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors shadow-sm">
                    Submit registration
                </button>
            </form>
        </div>

        {{-- Link to login --}}
        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign in</a>
        </p>
    </div>

</body>
</html>
