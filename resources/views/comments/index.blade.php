<x-layouts.app>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Comments & Discussions</h1>
                <p class="mt-1 text-sm text-gray-600">
                    View and manage comments on requirements and events
                </p>
            </div>

            {{-- Filters --}}
            <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
                <form method="GET" action="{{ route('comments.index') }}" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label for="event_id" class="block text-sm font-medium text-gray-700 mb-1">Filter by Event</label>
                        <select name="event_id" id="event_id" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" 
                                    {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->name }} ({{ $event->event_date->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 inline -mt-1 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Filter
                        </button>
                        @if(request()->hasAny(['event_id', 'requirement_id']))
                            <a href="{{ route('comments.index') }}" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Comments List --}}
            <div class="space-y-6">
                @forelse($comments as $comment)
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        {{-- Comment Header --}}
                        <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    @if($comment->requirement)
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-1">
                                                <div class="w-10 h-10 rounded-lg bg-indigo-500 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-base font-semibold text-gray-900">
                                                    {{ $comment->requirement->description }}
                                                </div>
                                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                                                    <span class="flex items-center gap-1.5 text-gray-700">
                                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span class="font-medium">{{ $comment->requirement->event->name }}</span>
                                                    </span>
                                                    <span class="flex items-center gap-1.5 text-gray-700">
                                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                        {{ $comment->requirement->department->name }}
                                                    </span>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold shadow-sm
                                                        {{ match($comment->requirement->status->value ?? $comment->requirement->status) {
                                                            'not_started' => 'bg-gray-200 text-gray-800',
                                                            'in_progress' => 'bg-blue-500 text-white',
                                                            'completed' => 'bg-green-500 text-white',
                                                            default => 'bg-gray-200 text-gray-800'
                                                        } }}">
                                                        {{ $comment->requirement->status->label() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($comment->event)
                                        <div class="text-base font-semibold text-gray-900">
                                            📅 {{ $comment->event->name }}
                                        </div>
                                        <div class="mt-1 text-sm text-gray-600">
                                            {{ $comment->event->event_date->format('F j, Y') }}
                                        </div>
                                    @endif
                                </div>

                                @if($comment->requirement)
                                    <a href="{{ route('events.show', $comment->requirement->event_id) }}" 
                                        class="ml-4 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                                        View Details →
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Main Comment Container --}}
                        <div class="p-6 bg-gradient-to-br from-amber-50 to-orange-50 border-b-4 border-amber-200">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-base font-bold text-gray-900">{{ $comment->user->name }}</span>
                                            <span class="text-gray-400">•</span>
                                            <span class="text-sm text-gray-600 font-medium">{{ $comment->created_at->diffForHumans() }}</span>
                                            @if($comment->via_email ?? false)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-500 text-white shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    via email
                                                </span>
                                            @endif
                                        </div>
                                        @if(Auth::id() === $comment->user_id || Auth::user()->isAdmin())
                                            <form method="POST" action="{{ route('comments.destroy', $comment) }}" 
                                                onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="mt-3 bg-white rounded-lg p-4 shadow-sm border border-amber-200">
                                        <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $comment->message }}</p>
                                    </div>

                                    {{-- Reply Button --}}
                                    <button onclick="toggleReplyForm('comment-{{ $comment->id }}')" 
                                        class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        Reply
                                    </button>

                                    {{-- Reply Form (hidden by default) --}}
                                    <div id="reply-form-comment-{{ $comment->id }}" class="hidden mt-4 p-4 bg-white rounded-lg border-2 border-indigo-200 shadow-sm">
                                        <form method="POST" action="{{ route('comments.store') }}" class="space-y-3">
                                            @csrf
                                            <input type="hidden" name="commentable_type" value="App\Models\Requirement">
                                            <input type="hidden" name="commentable_id" value="{{ $comment->requirement_id }}">
                                            <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">
                                            
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Write your reply</label>
                                            <textarea name="body" rows="3" required
                                                placeholder="Type your reply here..."
                                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
                                            
                                            <div class="flex gap-3">
                                                <button type="submit" 
                                                    class="px-5 py-2.5 bg-yellow-400 text-gray-900 text-sm font-bold rounded-lg hover:bg-yellow-500 shadow-md transition-all hover:shadow-lg">
                                                    Post Reply
                                                </button>
                                                <button type="button" onclick="toggleReplyForm('comment-{{ $comment->id }}')"
                                                    class="px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    {{-- Replies --}}
                                    @if($comment->replies->count() > 0)
                                        <div class="mt-5 space-y-4">
                                            @foreach($comment->replies as $reply)
                                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border-l-4 border-indigo-400 shadow-sm">
                                                    <div class="flex items-start gap-3">
                                                        <div class="flex-shrink-0">
                                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                                                {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                                            </div>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center justify-between flex-wrap gap-2">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-sm font-bold text-gray-900">{{ $reply->user->name }}</span>
                                                                    <span class="text-gray-400">•</span>
                                                                    <span class="text-xs text-gray-600">{{ $reply->created_at->diffForHumans() }}</span>
                                                                </div>
                                                                @if(Auth::id() === $reply->user_id || Auth::user()->isAdmin())
                                                                    <form method="POST" action="{{ route('comments.destroy', $reply) }}" 
                                                                        onsubmit="return confirm('Are you sure you want to delete this reply?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-semibold">
                                                                            Delete
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                            <div class="mt-2 bg-white rounded-lg p-3 shadow-sm">
                                                                <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $reply->message }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No comments yet</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Comments will appear here when users post them on requirements or events.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($comments->hasPages())
                <div class="mt-6">
                    {{ $comments->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleReplyForm(commentId) {
            const form = document.getElementById('reply-form-' + commentId);
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                form.querySelector('textarea').focus();
            } else {
                form.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-layouts.app>
