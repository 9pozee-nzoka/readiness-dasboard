<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === auth()->id(), 403);

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
