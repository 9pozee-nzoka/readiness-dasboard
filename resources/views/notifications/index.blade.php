<x-layouts.app>
    <x-slot:title>Notifications</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">
                @if ($unreadCount > 0)
                    <span class="font-medium text-indigo-600">{{ $unreadCount }} unread</span>
                @else
                    No unread notifications
                @endif
            </p>
        </div>
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf @method('PATCH')
                <button type="submit"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p class="text-gray-500 font-medium">No notifications yet</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <ul class="divide-y divide-gray-100">
                @foreach ($notifications as $notification)
                    <li class="px-6 py-4 flex items-start gap-4 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/30' }}">
                        <div class="mt-0.5 shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                            {{ $notification->read_at ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 {{ $notification->read_at ? '' : 'font-medium' }}">
                                {{ $notification->data['message'] ?? 'You have a new notification.' }}
                            </p>
                            @if (!empty($notification->data['event_name']))
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Event: {{ $notification->data['event_name'] }}
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if (! $notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="shrink-0">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-700 px-2 py-1 rounded hover:bg-indigo-50 transition-colors">
                                    Mark read
                                </button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</x-layouts.app>
