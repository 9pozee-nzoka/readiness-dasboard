<?php

use App\Http\Controllers\Admin\AccommodationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminVisitorController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\EventTypeRequirementController;
use App\Http\Controllers\Admin\OutsourcedServiceController;
use App\Http\Controllers\Admin\PlanningWeekController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentReplyWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailReplyController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\RecurringIssueController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequirementController;
use Illuminate\Support\Facades\Route;

// ── Webhook endpoints (public) ────────────────────────────────────
Route::post('webhooks/email-reply', [EmailReplyController::class, 'handleIncoming'])->name('webhooks.email-reply');
Route::post('webhooks/comments/email-reply', [CommentReplyWebhookController::class, 'handleEmailReply'])->name('webhooks.comments.email');
Route::post('webhooks/comments/whatsapp-reply', [CommentReplyWebhookController::class, 'handleWhatsAppReply'])->name('webhooks.comments.whatsapp');

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
    Route::patch('requirements/{requirement}/priority', [RequirementController::class, 'updatePriority'])->name('requirements.update-priority');
    Route::patch('requirements/{requirement}/assign', [RequirementController::class, 'assign'])->name('requirements.assign');
    Route::patch('requirements/{requirement}/escalate', [RequirementController::class, 'escalate'])->name('requirements.escalate');
    Route::post('requirements/{requirement}/verify', [RequirementController::class, 'verify'])->name('requirements.verify');
    Route::post('requirements/{requirement}/unverify', [RequirementController::class, 'unverify'])->name('requirements.unverify');
    Route::delete('requirements/{requirement}', [RequirementController::class, 'destroy'])->name('requirements.destroy');

    // Quick actions from email (signed routes)
    Route::get('requirements/{requirement}/acknowledge', [RequirementController::class, 'acknowledge'])
        ->name('requirements.acknowledge')->middleware('signed');
    Route::get('requirements/{requirement}/quick-complete', [RequirementController::class, 'quickComplete'])
        ->name('requirements.quick-complete')->middleware('signed');
    Route::get('requirements/{requirement}/quick-escalate', [RequirementController::class, 'quickEscalate'])
        ->name('requirements.quick-escalate')->middleware('signed');

    // Reminders
    Route::post('events/{event}/reminders', [ReminderController::class, 'send'])->name('reminders.send');

    // Notifications
    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationsController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationsController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Comments
    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Print report
    Route::get('events/{event}/print', [EventController::class, 'printReport'])->name('events.print');

    // ── Reports ───────────────────────────────────────────────────
    Route::get('reports/events/{event}', [ReportController::class, 'eventReport'])->name('reports.event');
    Route::get('reports/weekly-schedule', [ReportController::class, 'weeklySchedule'])->name('reports.weekly-schedule');
    Route::get('reports/weekly-csv', [ReportController::class, 'weeklyCsv'])->name('reports.weekly-csv');

    // ── Knowledge Base ────────────────────────────────────────────
    Route::get('knowledge-base', [KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
    Route::get('knowledge-base/{postEventReview}', [KnowledgeBaseController::class, 'show'])->name('knowledge-base.show');

    // ── Recurring Issues ──────────────────────────────────────────
    Route::get('recurring-issues', [RecurringIssueController::class, 'index'])->name('recurring-issues.index');
    Route::get('recurring-issues/create', [RecurringIssueController::class, 'create'])->name('recurring-issues.create');
    Route::post('recurring-issues', [RecurringIssueController::class, 'store'])->name('recurring-issues.store');
    Route::get('recurring-issues/{recurringIssue}', [RecurringIssueController::class, 'show'])->name('recurring-issues.show');
    Route::get('recurring-issues/{recurringIssue}/edit', [RecurringIssueController::class, 'edit'])->name('recurring-issues.edit');
    Route::put('recurring-issues/{recurringIssue}', [RecurringIssueController::class, 'update'])->name('recurring-issues.update');
    Route::delete('recurring-issues/{recurringIssue}', [RecurringIssueController::class, 'destroy'])->name('recurring-issues.destroy');
    Route::patch('recurring-issues/{recurringIssue}/resolve', [RecurringIssueController::class, 'resolve'])->name('recurring-issues.resolve');
    Route::post('recurring-issues/{recurringIssue}/occurrences', [RecurringIssueController::class, 'logOccurrence'])->name('recurring-issues.occurrences.store');

    // ── Director/Admin: outsourced services ────────────────────────
    Route::middleware('role:admin,director')->prefix('admin')->name('admin.')->group(function () {
        // Outsourced services (Director/Admin only - authorization in controller)
        Route::get('outsourced-services', [OutsourcedServiceController::class, 'index'])->name('outsourced-services.index');
        Route::get('outsourced-services/create', [OutsourcedServiceController::class, 'create'])->name('outsourced-services.create');
        Route::post('outsourced-services', [OutsourcedServiceController::class, 'store'])->name('outsourced-services.store');
        Route::get('outsourced-services/{outsourcedService}', [OutsourcedServiceController::class, 'show'])->name('outsourced-services.show');
        Route::get('outsourced-services/{outsourcedService}/edit', [OutsourcedServiceController::class, 'edit'])->name('outsourced-services.edit');
        Route::put('outsourced-services/{outsourcedService}', [OutsourcedServiceController::class, 'update'])->name('outsourced-services.update');
        Route::post('outsourced-services/{outsourcedService}/approve', [OutsourcedServiceController::class, 'approve'])->name('outsourced-services.approve');
        Route::post('outsourced-services/{outsourcedService}/reject', [OutsourcedServiceController::class, 'reject'])->name('outsourced-services.reject');
        Route::delete('outsourced-services/{outsourcedService}', [OutsourcedServiceController::class, 'destroy'])->name('outsourced-services.destroy');
    });

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
        Route::post('planning-weeks/{planning_week}/share', [PlanningWeekController::class, 'share'])->name('planning-weeks.share');
        Route::post('planning-weeks/{planning_week}/share-all', [PlanningWeekController::class, 'shareAll'])->name('planning-weeks.share-all');
        Route::get('planning-weeks/{planning_week}/accommodation', [AccommodationController::class, 'edit'])->name('accommodation.edit');
        Route::put('planning-weeks/{planning_week}/accommodation', [AccommodationController::class, 'update'])->name('accommodation.update');
        Route::post('planning-weeks/{planning_week}/accommodation/prefill', [AccommodationController::class, 'prefillFromPrevious'])->name('accommodation.prefill');
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
