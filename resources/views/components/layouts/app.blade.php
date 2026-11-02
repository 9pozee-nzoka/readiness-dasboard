<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Event Readiness Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-green-50 text-gray-900 antialiased min-h-screen">

    {{-- ── Navigation ───────────────────────────────────────────────── --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm relative z-40"
         x-data="navMenu()">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16">

                {{-- Brand --}}
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2 min-w-0 shrink-0">
                    <div class="flex items-center">
                        <img src="{{ asset('assets/logos/1.png') }}" alt="" class="h-7 sm:h-8 w-auto object-contain">
                        <img src="{{ asset('assets/logos/2.png') }}" alt="" class="h-7 sm:h-8 w-auto object-contain">
                        <img src="{{ asset('assets/logos/3.png') }}" alt="" class="h-7 sm:h-8 w-auto object-contain">
                    </div>
                    <span class="font-semibold text-gray-900 text-base hidden md:block truncate">Readiness Dashboard</span>
                </a>

                {{-- Desktop centre links --}}
                @auth
                <div class="hidden lg:flex items-center gap-0.5">

                    <a href="{{ route('dashboard.index') }}"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                            {{ request()->routeIs('dashboard.index') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                        Dashboard
                    </a>

                    @if (auth()->user()->canCreateEvents())
                        <a href="{{ route('events.create') }}"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                                {{ request()->routeIs('events.create') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            Create Event
                        </a>
                    @endif

                    {{-- Knowledge Base dropdown --}}
                    <div class="relative" x-data="{ kbOpen: false }">
                        <button @click="kbOpen = !kbOpen" @click.outside="kbOpen = false"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                                {{ request()->routeIs('knowledge-base.*') || request()->routeIs('recurring-issues.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            Knowledge Base
                            <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="kbOpen ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="kbOpen" x-cloak x-transition
                            class="absolute left-0 top-full mt-1 w-52 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50">
                            <a href="{{ route('knowledge-base.index') }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                📚 Post-Event Reviews
                            </a>
                            <a href="{{ route('recurring-issues.index') }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                🔁 Recurring Issues
                            </a>
                        </div>
                    </div>

                    @php
                        // Count unread comments directed to current user
                        $unreadComments = \App\Models\Comment::query()
                            ->where(function($q) {
                                $user = auth()->user();
                                // Comments directed to this specific user
                                $q->where('directed_to_user_id', $user->id)
                                    // OR comments directed to user's role
                                    ->orWhere(function($q2) use ($user) {
                                        $q2->where('directed_to', $user->role->value)
                                            ->whereNull('directed_to_user_id');
                                    });
                            })
                            ->where('user_id', '!=', auth()->id()) // Not own comments
                            ->whereDoesntHave('replies', function($q) {
                                $q->where('user_id', auth()->id());
                            })
                            ->count();
                    @endphp

                    <a href="{{ route('comments.index') }}"
                        class="relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                            {{ request()->routeIs('comments.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                        Comments
                        @if ($unreadComments > 0)
                            <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">
                                {{ $unreadComments > 99 ? '99+' : $unreadComments }}
                            </span>
                        @endif
                    </a>
                </div>
                @endauth

                {{-- Right side: notifications + user + mobile hamburger --}}
                <div class="flex items-center gap-1 sm:gap-2">

                    @auth
                    {{-- Notifications bell --}}
                    @php $unreadNotifs = auth()->user()->unreadNotifications()->count(); @endphp
                    <a href="{{ route('notifications.index') }}"
                        class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg hover:bg-gray-100 transition-colors text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if ($unreadNotifs > 0)
                            <span class="absolute top-0.5 right-0.5 inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold">
                                {{ $unreadNotifs > 9 ? '9+' : $unreadNotifs }}
                            </span>
                        @endif
                    </a>

                    {{-- Desktop user menu --}}
                    <div class="relative hidden sm:block" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-gray-100 transition-colors text-sm">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs shrink-0">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="font-medium text-gray-900 leading-tight text-xs">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 leading-tight">{{ auth()->user()->role->label() }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                            class="absolute right-0 mt-1 w-52 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->email }}</p>
                                <p class="text-xs text-indigo-600 mt-0.5">{{ auth()->user()->role->label() }}
                                    @if (auth()->user()->department) · {{ auth()->user()->department->name }} @endif
                                </p>
                            </div>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    ⚙️ Admin Panel
                                </a>
                                <a href="{{ route('admin.users.index') }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    👥 Manage Users
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Mobile hamburger --}}
                    <button @click="mobileOpen = !mobileOpen"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 lg:hidden">
                        <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    @endauth
                </div>
            </div>
        </div>

        {{-- ── Mobile menu ── --}}
        @auth
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden border-t border-gray-100 bg-white">

            <div class="px-4 py-3 space-y-1">

                {{-- User info strip --}}
                <div class="flex items-center gap-3 px-3 py-3 mb-2 bg-gray-50 rounded-xl">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm shrink-0">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->role->label() }}
                            @if(auth()->user()->department) · {{ auth()->user()->department->name }} @endif
                        </p>
                    </div>
                </div>

                {{-- Nav links --}}
                @php
                    $mobileLinks = [
                        ['route' => 'dashboard.index',        'label' => '🏠 Dashboard', 'badge' => null],
                    ];
                    
                    // Add Create Event link for users who can create events
                    if (auth()->user()->canCreateEvents()) {
                        $mobileLinks[] = ['route' => 'events.create', 'label' => '➕ Create Event', 'badge' => null];
                    }
                    
                    $mobileLinks = array_merge($mobileLinks, [
                        ['route' => 'comments.index',         'label' => '💬 Comments', 'badge' => $unreadComments],
                        ['route' => 'knowledge-base.index',   'label' => '📚 Post-Event Reviews', 'badge' => null],
                        ['route' => 'recurring-issues.index', 'label' => '🔁 Recurring Issues', 'badge' => null],
                    ]);
                @endphp

                @foreach ($mobileLinks as $link)
                    <a href="{{ route($link['route']) }}" @click="mobileOpen = false"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                            {{ request()->routeIs($link['route'].'*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        {{ $link['label'] }}
                        @if (isset($link['badge']) && $link['badge'] > 0)
                            <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold">
                                {{ $link['badge'] > 9 ? '9+' : $link['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach

                {{-- Admin links --}}
                @if (auth()->user()->isAdmin())
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <p class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Admin</p>
                        <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            ⚙️ Admin Panel
                        </a>
                        <a href="{{ route('admin.users.index') }}" @click="mobileOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            👥 Manage Users
                        </a>
                    </div>
                @endif

                {{-- Sign out --}}
                <div class="pt-2 mt-2 border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                            🚪 Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endauth
    </nav>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                {{ session('info') }}
            </div>
        </div>
    @endif

    @if (!empty($errors) && $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-200 mt-12 py-6 text-center text-xs text-gray-400">
        Weekly Event Readiness Dashboard &mdash; Internal Use Only
    </footer>

    @stack('scripts')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @auth
    <script>
    function navMenu() {
        return {
            mobileOpen: false
        };
    }
    </script>
    @endauth
</body>
</html>
