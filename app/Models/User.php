<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
            'is_approved'       => 'boolean',
            'approved_at'       => 'datetime',
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

    // ── Approval helpers ──────────────────────────────────────────

    public function isPending(): bool
    {
        return ! $this->is_approved;
    }

    public function approve(User $admin, UserRole $assignedRole): void
    {
        $this->update([
            'role'        => $assignedRole,
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

    public function isHod(): bool
    {
        return $this->role === UserRole::Hod;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
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
