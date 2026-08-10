<?php

namespace App\Enums;

enum ScheduleCategory: string
{
    case NewProgramme = 'new_programme';
    case Ongoing = 'ongoing';
    case Hosted = 'hosted';
    case KsgActivity = 'ksg_activity';
    case Consultancy = 'consultancy';

    public function label(): string
    {
        return match ($this) {
            self::NewProgramme => 'New Programmes',
            self::Ongoing => 'Ongoing Programmes / Events',
            self::Hosted => 'Hosted Events / Conferences',
            self::KsgActivity => 'KSG Activities',
            self::Consultancy => 'Consultancies & Research',
        };
    }

    public function eventType(): string
    {
        return match ($this) {
            self::NewProgramme, self::Ongoing => 'Training',
            self::Hosted => 'Conference',
            self::KsgActivity => 'Internal',
            self::Consultancy => 'Consultancy',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $category) => [$category->value => $category->label()])
            ->all();
    }
}
