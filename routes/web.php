<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminVisitorController;
use App\Http\Controllers\Admin\EventTypeRequirementController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReminderController;
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

    // Requirements — toggle open to hod/employee for their dept; store/destroy admin only (enforced in controller)
    Route::post('requirements', [RequirementController::class, 'store'])->name('requirements.store');
    Route::patch('requirements/{requirement}/toggle', [RequirementController::class, 'toggle'])->name('requirements.toggle');
    Route::patch('requirements/{requirement}/escalate', [RequirementController::class, 'escalate'])->name('requirements.escalate');
    Route::delete('requirements/{requirement}', [RequirementController::class, 'destroy'])->name('requirements.destroy');

    // Reminders — admin and HOD only (enforced in controller)
    Route::post('events/{event}/reminders', [ReminderController::class, 'send'])->name('reminders.send');

    // ── Admin: user management ────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Admin dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User approvals
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/revoke', [AdminUserController::class, 'revoke'])->name('users.revoke');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

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

        // Visitor tracking
        Route::get('visitors', [AdminVisitorController::class, 'index'])->name('visitors.index');

        // Event type requirement templates
        Route::get('event-type-requirements', [EventTypeRequirementController::class, 'index'])->name('event-type-requirements.index');
        Route::post('event-type-requirements', [EventTypeRequirementController::class, 'store'])->name('event-type-requirements.store');
        Route::put('event-type-requirements/{eventTypeRequirement}', [EventTypeRequirementController::class, 'update'])->name('event-type-requirements.update');
        Route::patch('event-type-requirements/{eventTypeRequirement}/toggle', [EventTypeRequirementController::class, 'toggle'])->name('event-type-requirements.toggle');
        Route::delete('event-type-requirements/{eventTypeRequirement}', [EventTypeRequirementController::class, 'destroy'])->name('event-type-requirements.destroy');
    });
});
