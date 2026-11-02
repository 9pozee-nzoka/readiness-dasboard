@props(['unreadCount' => 0, 'notifications' => []])

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" 
        type="button"
        class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
        aria-label="Notifications">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex items-center justify-center rounded-full h-5 w-5 bg-red-500 text-white text-xs font-bold">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="open" 
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-lg border border-gray-200 z-50 max-h-[32rem] flex flex-col"
        style="display: none;">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Notifications</h3>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="overflow-y-auto flex-1">
            @forelse($notifications as $notification)
                <a href="{{ $notification->action_url ?? '#' }}"
                    onclick="event.preventDefault(); 
                             fetch('{{ route('notifications.read', $notification) }}', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                             .then(() => window.location.href = '{{ $notification->action_url ?? '#' }}');"
                    class="block px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                    <div class="flex items-start gap-3">
                        {{-- Unread indicator --}}
                        <div class="shrink-0 mt-1">
                            @if(!$notification->read_at)
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            @else
                                <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm {{ $notification->read_at ? 'text-gray-700' : 'text-gray-900 font-medium' }}">
                                {{ $notification->title }}
                            </p>
                            @if($notification->body)
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                    {{ $notification->body }}
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Type badge --}}
                        <div class="shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium
                                {{ match($notification->type) {
                                    'assignment' => 'bg-indigo-100 text-indigo-800',
                                    'deadline_approaching' => 'bg-amber-100 text-amber-800',
                                    'overdue' => 'bg-red-100 text-red-800',
                                    'verification_requested' => 'bg-purple-100 text-purple-800',
                                    'verification_approved' => 'bg-green-100 text-green-800',
                                    'comment_posted' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                } }}">
                                {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-sm">No notifications yet</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            <a href="{{ route('notifications.index') }}" 
                class="block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                View all notifications
            </a>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
