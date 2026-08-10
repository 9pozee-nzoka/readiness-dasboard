<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Department;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $rooms = ChatRoom::with([
            'department',
            'event',
            'latestMessages.user',
        ])
            ->where('is_active', true)
            ->get()
            ->filter(fn ($room) => $room->canAccess($user))
            ->map(function ($room) use ($user) {
                $room->unread = $room->unreadCountFor($user);

                return $room;
            })
            ->values();

        $totalUnread = $rooms->sum('unread');

        return view('chat.index', compact('rooms', 'user', 'totalUnread'));
    }

    public function show(ChatRoom $chatRoom): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $chatRoom->canAccess($user)) {
            return redirect()->route('chat.index')
                ->with('info', 'You do not have access to that chat room.');
        }

        $messages = $chatRoom->messages()
            ->with('user')
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        // Mark all messages as read
        $chatRoom->markReadFor($user);

        $room = $chatRoom->load(['department', 'event']);

        return view('chat.show', compact('room', 'messages', 'user'));
    }

    public function sendMessage(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $user = Auth::user();

        if (! $chatRoom->canAccess($user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        $message->load('user');

        // Advance sender's read pointer immediately
        $chatRoom->markReadFor($user);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'id' => $message->id,
            'message' => $message->message,
            'created_at' => $message->created_at->format('H:i'),
            'date' => $message->created_at->format('d M Y'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role->label(),
            ],
        ]);
    }

    /** Mark all messages in a room as read for the current user. */
    public function markRead(ChatRoom $chatRoom): JsonResponse
    {
        $user = Auth::user();

        if (! $chatRoom->canAccess($user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $chatRoom->markReadFor($user);

        return response()->json(['ok' => true]);
    }

    /** Total unread message count across all accessible rooms. */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $rooms = ChatRoom::where('is_active', true)->get()
            ->filter(fn ($r) => $r->canAccess($user));

        $total = $rooms->sum(fn ($r) => $r->unreadCountFor($user));

        // Per-room breakdown for the notification popup
        $perRoom = $rooms
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'slug' => $r->slug,
                'url' => route('chat.show', $r),
                'count' => $r->unreadCountFor($user),
            ])
            ->filter(fn ($r) => $r['count'] > 0)
            ->values();

        return response()->json(['total' => $total, 'rooms' => $perRoom]);
    }

    public function loadMessages(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        if (! $chatRoom->canAccess(Auth::user())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $before = $request->integer('before', PHP_INT_MAX);

        $messages = $chatRoom->messages()
            ->with('user')
            ->where('id', '<', $before)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'created_at' => $m->created_at->format('H:i'),
                'date' => $m->created_at->format('d M Y'),
                'user' => [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                    'role' => $m->user->role->label(),
                ],
            ]);

        return response()->json([
            'messages' => $messages,
            'has_more' => $messages->count() >= 50,
        ]);
    }

    public function createRooms(): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        Department::where('is_active', true)->each(function (Department $dept) {
            ChatRoom::firstOrCreate(
                ['slug' => 'dept-'.Str::slug($dept->name)],
                [
                    'name' => $dept->name.' — Department Chat',
                    'type' => 'department',
                    'department_id' => $dept->id,
                    'description' => 'Readiness deliberations for the '.$dept->name.' department.',
                    'is_active' => true,
                ]
            );
        });

        Event::where('event_date', '>=', now()->toDateString())
            ->each(function (Event $event) {
                ChatRoom::firstOrCreate(
                    ['slug' => 'event-'.Str::slug($event->name).'-'.$event->id],
                    [
                        'name' => $event->name.' — Event Chat',
                        'type' => 'event',
                        'event_id' => $event->id,
                        'description' => 'Coordination chat for '.$event->name,
                        'is_active' => true,
                    ]
                );
            });

        ChatRoom::firstOrCreate(
            ['slug' => 'general'],
            [
                'name' => 'General — All Staff',
                'type' => 'general',
                'description' => 'Organisation-wide announcements and discussion.',
                'is_active' => true,
            ]
        );

        return redirect()->route('chat.index')
            ->with('success', 'Chat rooms created successfully.');
    }
}
