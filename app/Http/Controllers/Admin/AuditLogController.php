<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(Request $request): View
    {
        $eventId = $request->integer('event');
        $userId = $request->integer('user');
        $action = $request->string('action')->trim()->value();
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        $logs = AuditLog::with(['user', 'event', 'requirement'])
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $events = Event::orderBy('name')->get();
        $users = User::where('is_approved', true)->orderBy('name')->get();
        $actions = ['created', 'completed', 'pending', 'escalated', 'updated', 'deleted'];

        return view('admin.audit-log.index', compact(
            'logs', 'events', 'users', 'actions',
            'eventId', 'userId', 'action', 'dateFrom', 'dateTo'
        ));
    }
}
