<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Director = 'director';
    case Hod = 'hod';
    case Employee = 'employee';
    case CourseCoordinator = 'course_coordinator';
    case ProgrammeAssistant = 'programme_assistant';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Director => 'Director',
            self::Hod => 'Head of Department',
            self::Employee => 'Employee',
            self::CourseCoordinator => 'Course Coordinator',
            self::ProgrammeAssistant => 'Programme Assistant',
        };
    }

    /** Roles that are scoped to a single department. */
    public function isDepartmentScoped(): bool
    {
        return in_array($this, [self::Hod, self::Employee]);
    }

    /** Roles that can manage events and requirements. */
    public function canManageEvents(): bool
    {
        return in_array($this, [self::Admin, self::CourseCoordinator, self::ProgrammeAssistant]);
    }

    /** Roles that can send reminders. */
    public function canSendReminders(): bool
    {
        return in_array($this, [self::Admin, self::Hod]);
    }
}
