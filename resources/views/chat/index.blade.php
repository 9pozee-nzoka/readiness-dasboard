<x-layouts.app>
    <x-slot:title>Chat Rooms</x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chat Rooms</h1>
            <p class="text-sm text-gray-500 mt-1">Department discussions and event coordination</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($totalUnread > 0)
                <span class="inline-flex items-center gap-1.5 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                    {{ $totalUnread }} unread message{{ $totalUnread !== 1 ? 's' : '' }}
                </span>
            @endif
            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('chat.setup') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 border border-gray-300 hover:border-indigo-400 text-gray-700 hover:text-indigo-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sync Rooms
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($rooms->isEmpty())
        <div class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-gray-500 font-medium">No chat rooms available</p>
            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('chat.setup') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Create Chat Rooms
                    </button>
                </form>
            @endif
        </div>
    @else
        @php
            $grouped    = $rooms->groupBy('type');
            $typeLabels = ['general' => 'General', 'department' => 'Department Rooms', 'event' => 'Event Rooms'];
            $typeOrder  = ['general', 'department', 'event'];
        @endphp

        <div class="space-y-8">
            @foreach ($typeOrder as $type)
                @if ($grouped->has($type))
                    <div>
                        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">
                            {{ $typeLabels[$type] ?? $type }}
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($grouped[$type] as $room)
                                @php
                                    $latest = $room->latestMessages->first();
                                    $unread = $room->unread ?? 0;
                                @endphp
                                <a href="{{ route('chat.show', $room) }}"
                                    class="group relative bg-white rounded-xl border shadow-sm hover:shadow-md p-4 transition-all flex items-start gap-3
                                        {{ $unread > 0 ? 'border-indigo-200 bg-indigo-50/30' : 'border-gray-200 hover:border-indigo-300' }}">

                                    {{-- Icon --}}
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5
                                        {{ $room->type === 'general' ? 'bg-indigo-100' : ($room->type === 'event' ? 'bg-amber-100' : 'bg-green-100') }}">
                                        @if ($room->type === 'department')
                                            <div class="w-4 h-4 rounded-full" style="background-color: {{ $room->department?->color ?? '#6366f1' }}"></div>
                                        @elseif ($room->type === 'event')
                                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="font-semibold text-sm truncate
                                                {{ $unread > 0 ? 'text-indigo-800' : 'text-gray-900 group-hover:text-indigo-700' }}">
                                                {{ $room->name }}
                                            </p>
                                            @if ($unread > 0)
                                                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold shrink-0">
                                                    {{ $unread > 99 ? '99+' : $unread }}
                                                </span>
                                            @endif
                                        </div>

                                        @if ($latest)
                                            <p class="text-xs mt-0.5 truncate {{ $unread > 0 ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">
                                                <span class="{{ $unread > 0 ? 'font-semibold' : 'font-medium text-gray-500' }}">
                                                    {{ $latest->user?->name ?? 'Unknown' }}:
                                                </span>
                                                {{ Str::limit($latest->message, 55) }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $latest->created_at->diffForHumans() }}</p>
                                        @else
                                            <p class="text-xs text-gray-400 mt-0.5 italic">No messages yet — start the conversation</p>
                                        @endif
                                    </div>

                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 shrink-0 mt-3 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>

                                    {{-- Unread dot indicator --}}
                                    @if ($unread > 0)
                                        <div class="absolute top-3 right-3 w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</x-layouts.app>
