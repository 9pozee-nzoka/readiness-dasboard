<x-layouts.app>
    <x-slot:title>{{ $room->name }}</x-slot:title>

    <style>
        #chat-messages { scroll-behavior: smooth; }
        .message-bubble { animation: fadeInUp 0.15s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('chat.index') }}" class="hover:text-indigo-600">Chat Rooms</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium truncate">{{ $room->name }}</span>
    </nav>

    <div class="flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden"
         style="height: calc(100dvh - 160px); min-height: 400px;"
         x-data="chatRoom({{ $room->id }}, {{ auth()->id() }})"
         x-init="init()">

        {{-- Room header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50/60">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
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
                <h1 class="font-semibold text-gray-900 text-sm truncate">{{ $room->name }}</h1>
                @if ($room->description)
                    <p class="text-xs text-gray-400 truncate">{{ $room->description }}</p>
                @endif
            </div>
            {{-- Online indicator --}}
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span class="text-xs text-gray-400" x-text="onlineCount + ' online'"></span>
            </div>
        </div>

        {{-- Load earlier messages --}}
        <div class="px-5 pt-3 text-center" x-show="hasMore">
            <button type="button" @click="loadEarlier()"
                class="text-xs text-indigo-600 hover:text-indigo-700 font-medium"
                :disabled="loadingMore"
                x-text="loadingMore ? 'Loading…' : 'Load earlier messages'">
            </button>
        </div>

        {{-- Messages area --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto px-5 py-4 space-y-3"
             x-ref="messageContainer">

            {{-- Server-rendered messages (initial load) --}}
            @foreach ($messages as $msg)
                @php $isMine = $msg->user_id === auth()->id(); @endphp
                <div class="flex items-end gap-2 {{ $isMine ? 'flex-row-reverse' : '' }}">
                    {{-- Avatar --}}
                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs shrink-0 mb-1">
                        {{ mb_strtoupper(mb_substr($msg->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="max-w-[70%] space-y-0.5">
                        @unless ($isMine)
                            <p class="text-[11px] text-gray-400 px-1">{{ $msg->user->name }} · {{ $msg->user->role->label() }}</p>
                        @endunless
                        <div class="px-3.5 py-2 rounded-2xl text-sm leading-relaxed break-words
                            {{ $isMine
                                ? 'bg-indigo-600 text-white rounded-br-sm'
                                : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                            {{ $msg->message }}
                        </div>
                        <p class="text-[10px] text-gray-400 px-1 {{ $isMine ? 'text-right' : '' }}">
                            {{ $msg->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>
            @endforeach

            {{-- Dynamically added messages via Alpine --}}
            <template x-for="msg in newMessages" :key="msg.id">
                <div class="flex items-end gap-2 message-bubble"
                     :class="msg.user.id === currentUserId ? 'flex-row-reverse' : ''">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs shrink-0 mb-1"
                         x-text="msg.user.name.charAt(0).toUpperCase()"></div>
                    <div class="max-w-[70%] space-y-0.5">
                        <template x-if="msg.user.id !== currentUserId">
                            <p class="text-[11px] text-gray-400 px-1" x-text="msg.user.name + ' · ' + msg.user.role"></p>
                        </template>
                        <div class="px-3.5 py-2 rounded-2xl text-sm leading-relaxed break-words"
                             :class="msg.user.id === currentUserId
                                ? 'bg-indigo-600 text-white rounded-br-sm'
                                : 'bg-gray-100 text-gray-800 rounded-bl-sm'"
                             x-text="msg.message"></div>
                        <p class="text-[10px] text-gray-400 px-1"
                           :class="msg.user.id === currentUserId ? 'text-right' : ''"
                           x-text="msg.created_at"></p>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="typingUsers.length > 0" class="flex items-end gap-2">
                <div class="w-7 h-7 rounded-full bg-gray-100 shrink-0"></div>
                <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-2.5">
                    <div class="flex gap-1 items-center h-4">
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
                <p class="text-[10px] text-gray-400 mb-1" x-text="typingText"></p>
            </div>
        </div>

        {{-- Message input --}}
        <div class="border-t border-gray-100 px-4 py-3 bg-white">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                <div class="flex-1 relative">
                    <textarea
                        x-ref="input"
                        x-model="draft"
                        @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                        @input="onTyping()"
                        placeholder="Type a message… (Enter to send, Shift+Enter for new line)"
                        rows="1"
                        class="block w-full resize-none rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 transition leading-relaxed max-h-32 overflow-y-auto"
                        :disabled="sending"
                    ></textarea>
                </div>
                <button type="submit"
                    :disabled="!draft.trim() || sending"
                    class="w-10 h-10 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const ROOM_ID = {{ $room->id }};
    const REVERB_KEY  = '{{ config('reverb.apps.apps.0.key', env('VITE_REVERB_APP_KEY', '')) }}';
    const REVERB_HOST = '{{ config('reverb.servers.reverb.host', '127.0.0.1') }}';
    const REVERB_PORT = {{ config('reverb.servers.reverb.port', 8080) }};
    const REVERB_SCHEME = '{{ config('reverb.servers.reverb.scheme', 'http') }}';

    function chatRoom(roomId, currentUserId) {
        return {
            roomId,
            currentUserId,
            draft: '',
            sending: false,
            newMessages: [],
            hasMore: {{ $messages->count() >= 100 ? 'true' : 'false' }},
            loadingMore: false,
            oldestId: {{ $messages->first()?->id ?? 'null' }},
            typingUsers: [],
            typingText: '',
            typingTimer: null,
            onlineCount: 1,
            echo: null,

            init() {
                this.scrollToBottom();
                this.connectEcho();
                // Mark room as read on open
                this.markRead();
            },

            async markRead() {
                await fetch(`/chat/${this.roomId}/read`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
                });
                // Trigger nav badge refresh
                document.dispatchEvent(new CustomEvent('chat-new-message'));
            },

            connectEcho() {
                if (typeof window.Echo === 'undefined') return;

                this.echo = window.Echo.private(`chat-room.${this.roomId}`)
                    .listen('.message.sent', (data) => {
                        this.newMessages.push(data);
                        this.$nextTick(() => this.scrollToBottom());
                        // Auto-mark read if window is focused
                        if (document.hasFocus()) {
                            this.markRead();
                        }
                    })
                    .listenForWhisper('typing', (data) => {
                        if (data.userId === this.currentUserId) return;
                        if (!this.typingUsers.includes(data.name)) {
                            this.typingUsers.push(data.name);
                        }
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => { this.typingUsers = []; }, 2500);
                        this.typingText = this.typingUsers.join(', ') + (this.typingUsers.length === 1 ? ' is typing…' : ' are typing…');
                    });
            },

            async sendMessage() {
                if (!this.draft.trim() || this.sending) return;
                const text = this.draft;
                this.draft = '';
                this.sending = true;
                try {
                    const res = await fetch(`/chat/${this.roomId}/messages`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ message: text }),
                    });
                    if (!res.ok) throw new Error('Send failed');
                    const msg = await res.json();
                    this.newMessages.push(msg);
                    this.$nextTick(() => { this.scrollToBottom(); this.$refs.input.focus(); });
                } catch (e) {
                    this.draft = text; // restore on failure
                    console.error(e);
                } finally {
                    this.sending = false;
                }
            },

            async loadEarlier() {
                if (this.loadingMore) return;
                this.loadingMore = true;
                const container = this.$refs.messageContainer;
                const prevHeight = container.scrollHeight;
                try {
                    const res = await fetch(`/chat/${this.roomId}/messages?before=${this.oldestId}`, {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await res.json();
                    if (data.messages.length === 0) { this.hasMore = false; return; }
                    // Prepend to DOM (handled server-side via Blade on next page load, here we just refresh)
                    this.hasMore = data.messages.length >= 50;
                    if (data.messages.length > 0) this.oldestId = data.messages[0].id;
                    // For simplicity, reload page to show earlier messages
                    window.location.reload();
                } finally {
                    this.loadingMore = false;
                }
            },

            onTyping() {
                if (this.echo && typeof this.echo.whisper === 'function') {
                    this.echo.whisper('typing', { userId: this.currentUserId, name: '{{ auth()->user()->name }}' });
                }
            },

            scrollToBottom() {
                const c = this.$refs.messageContainer;
                if (c) c.scrollTop = c.scrollHeight;
            },
        };
    }
    </script>
    @endpush
</x-layouts.app>
