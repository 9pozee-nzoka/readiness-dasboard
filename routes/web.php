<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminVisitorController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\EventTypeRequirementController;
use App\Http\Controllers\Admin\PlanningWeekController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliberationApprovalController;
use App\Http\Controllers\DeliberationAttachmentController;
use App\Http\Controllers\DeliberationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequirementController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->middleware('throttle:10,1');
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated routes ──────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard — all roles
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    // Events — all roles can view; create/edit/delete restricted inside controller
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('events/{event}/export', [EventController::class, 'exportCsv'])->name('events.export');

    // Requirements — status update, assignment, escalation
    Route::post('requirements', [RequirementController::class, 'store'])->name('requirements.store');
    Route::patch('requirements/{requirement}/status', [RequirementController::class, 'updateStatus'])->name('requirements.status');
    Route::patch('requirements/{requirement}/assign', [RequirementController::class, 'assign'])->name('requirements.assign');
    Route::patch('requirements/{requirement}/escalate', [RequirementController::class, 'escalate'])->name('requirements.escalate');
    Route::delete('requirements/{requirement}', [RequirementController::class, 'destroy'])->name('requirements.destroy');

    // Reminders
    Route::post('events/{event}/reminders', [ReminderController::class, 'send'])->name('reminders.send');

    // Notifications
    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationsController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationsController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Print report
    Route::get('events/{event}/print', [EventController::class, 'printReport'])->name('events.print');

    // Deliberations
    Route::get('events/{event}/deliberations', [DeliberationController::class, 'index'])->name('deliberations.index');
    Route::get('events/{event}/deliberations/create', [DeliberationController::class, 'create'])->name('deliberations.create');
    Route::post('events/{event}/deliberations', [DeliberationController::class, 'store'])->name('deliberations.store');
    Route::get('events/{event}/deliberations/{deliberation}', [DeliberationController::class, 'show'])->name('deliberations.show');
    Route::get('events/{event}/deliberations/{deliberation}/edit', [DeliberationController::class, 'edit'])->name('deliberations.edit');
    Route::put('events/{event}/deliberations/{deliberation}', [DeliberationController::class, 'update'])->name('deliberations.update');
    Route::delete('events/{event}/deliberations/{deliberation}', [DeliberationController::class, 'destroy'])->name('deliberations.destroy');
    Route::post('events/{event}/deliberations/{deliberation}/comments', [DeliberationController::class, 'addComment'])->name('deliberations.comments.store');
    Route::patch('events/{event}/deliberations/{deliberation}/close', [DeliberationController::class, 'close'])->name('deliberations.close');
    Route::patch('events/{event}/deliberations/{deliberation}/escalate', [DeliberationController::class, 'escalate'])->name('deliberations.escalate');
    Route::post('events/{event}/deliberations/{deliberation}/approve', [DeliberationApprovalController::class, 'approve'])->name('deliberations.approve');
    Route::post('events/{event}/deliberations/{deliberation}/reject', [DeliberationApprovalController::class, 'reject'])->name('deliberations.reject');
    Route::post('events/{event}/deliberations/{deliberation}/attachments', [DeliberationAttachmentController::class, 'store'])->name('deliberations.attachments.store');
    Route::get('events/{event}/deliberations/{deliberation}/attachments/{attachment}', [DeliberationAttachmentController::class, 'download'])->name('deliberations.attachments.download');
    Route::get('events/{event}/deliberations/{deliberation}/attachments/{attachment}/preview', [DeliberationAttachmentController::class, 'preview'])->name('deliberations.attachments.preview');
    Route::delete('events/{event}/deliberations/{deliberation}/attachments/{attachment}', [DeliberationAttachmentController::class, 'destroy'])->name('deliberations.attachments.destroy');

    // ── Chat rooms ────────────────────────────────────────────────
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/unread', [ChatController::class, 'unreadCount'])->name('chat.unread');
    Route::get('chat/{chatRoom}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{chatRoom}/messages', [ChatController::class, 'sendMessage'])->name('chat.messages.send');
    Route::get('chat/{chatRoom}/messages', [ChatController::class, 'loadMessages'])->name('chat.messages.load');
    Route::patch('chat/{chatRoom}/read', [ChatController::class, 'markRead'])->name('chat.read');
    Route::post('chat/setup', [ChatController::class, 'createRooms'])->name('chat.setup');

    // ── Reports ───────────────────────────────────────────────────
    Route::get('reports/events/{event}', [ReportController::class, 'eventReport'])->name('reports.event');
    Route::get('reports/weekly-csv', [ReportController::class, 'weeklyCsv'])->name('reports.weekly-csv');

    // ── Knowledge Base ────────────────────────────────────────────
    Route::get('knowledge-base', [KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
    Route::get('knowledge-base/{postEventReview}', [KnowledgeBaseController::class, 'show'])->name('knowledge-base.show');

    // ── Admin: user management ────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Admin dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User approvals
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/revoke', [AdminUserController::class, 'revoke'])->name('users.revoke');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Planning weeks
        Route::get('planning-weeks/import', [PlanningWeekController::class, 'importForm'])->name('planning-weeks.import');
        Route::post('planning-weeks/import', [PlanningWeekController::class, 'import'])->name('planning-weeks.import.store');
        Route::patch('planning-weeks/{planning_week}/current', [PlanningWeekController::class, 'setCurrent'])->name('planning-weeks.set-current');
        Route::resource('planning-weeks', PlanningWeekController::class);

        // Events management
        Route::get('events', [AdminEventController::class, 'index'])->name('events.index');
        Route::get('events/create', [AdminEventController::class, 'create'])->name('events.create');
        Route::post('events', [AdminEventController::class, 'store'])->name('events.store');
        Route::get('events/{event}', [AdminEventController::class, 'show'])->name('events.show');
        Route::get('events/{event}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
        Route::put('events/{event}', [AdminEventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
        Route::post('events/{event}/requirements', [AdminEventController::class, 'addRequirement'])->name('events.requirements.add');
        Route::delete('events/{event}/requirements/{requirement}', [AdminEventController::class, 'removeRequirement'])->name('events.requirements.remove');

        // Staff performance
        Route::get('staff', [AdminStaffController::class, 'index'])->name('staff.index');
        Route::get('staff/create', [AdminStaffController::class, 'create'])->name('staff.create');
        Route::post('staff', [AdminStaffController::class, 'store'])->name('staff.store');

        // Visitor tracking
        Route::get('visitors', [AdminVisitorController::class, 'index'])->name('visitors.index');

        // Audit log
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

        // Event type requirement templates
        Route::get('event-type-requirements', [EventTypeRequirementController::class, 'index'])->name('event-type-requirements.index');
        Route::post('event-type-requirements', [EventTypeRequirementController::class, 'store'])->name('event-type-requirements.store');
        Route::put('event-type-requirements/{eventTypeRequirement}', [EventTypeRequirementController::class, 'update'])->name('event-type-requirements.update');
        Route::patch('event-type-requirements/{eventTypeRequirement}/toggle', [EventTypeRequirementController::class, 'toggle'])->name('event-type-requirements.toggle');
        Route::delete('event-type-requirements/{eventTypeRequirement}', [EventTypeRequirementController::class, 'destroy'])->name('event-type-requirements.destroy');
    });
});
