<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'declared_level',
        'is_approved',
        'approved_at',
        'approved_by',
        'whatsapp_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignedRequirements(): HasMany
    {
        return $this->hasMany(Requirement::class, 'assigned_to');
    }

    public function directedDeliberations(): BelongsToMany
    {
        return $this->belongsToMany(Deliberation::class, 'deliberation_participant')
            ->withPivot(['approved_at', 'rejected_at', 'notes', 'created_at'])
            ->withTimestamps();
    }

    public function ownedRisks(): HasMany
    {
        return $this->hasMany(Risk::class, 'owner_id');
    }

    /**
     * Override Notifiable trait's notifications() to use custom notifications table
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Get unread notifications for this user
     */
    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id')->whereNull('read_at');
    }

    // ── Approval helpers ──────────────────────────────────────────

    public function isPending(): bool
    {
        return ! $this->is_approved;
    }

    public function approve(User $admin, UserRole $assignedRole): void
    {
        $this->update([
            'role' => $assignedRole,
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);
    }

    // ── Role helpers ──────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isDirector(): bool
    {
        return $this->role === UserRole::Director;
    }

    public function isDirectorOrAdmin(): bool
    {
        return $this->isDirector() || $this->isAdmin();
    }

    public function isHod(): bool
    {
        return $this->role === UserRole::Hod;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    public function isCourseCoordinator(): bool
    {
        return $this->role === UserRole::CourseCoordinator;
    }

    public function isProgrammeAssistant(): bool
    {
        return $this->role === UserRole::ProgrammeAssistant;
    }

    public function canCreateEvents(): bool
    {
        return $this->role?->canManageEvents() ?? false;
    }

    public function hasRole(UserRole|string $role): bool
    {
        $enum = $role instanceof UserRole ? $role : UserRole::from($role);

        return $this->role === $enum;
    }

    public function canManageEvents(): bool
    {
        return $this->role->canManageEvents();
    }

    public function canSendReminders(): bool
    {
        return $this->role->canSendReminders();
    }

    public function isDepartmentScoped(): bool
    {
        return $this->role->isDepartmentScoped();
    }
}
