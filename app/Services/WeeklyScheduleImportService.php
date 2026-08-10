<?php

namespace App\Services;

use App\Enums\ScheduleCategory;
use App\Models\Event;
use App\Models\PlanningWeek;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WeeklyScheduleImportService
{
    /**
     * @return array{week: PlanningWeek, events_created: int, events_skipped: int}
     */
    public function importFromArray(array $payload, bool $replaceExisting = false): array
    {
        $this->validatePayload($payload);

        $weekStart = Carbon::parse($payload['week_start'])->startOfDay();
        $weekEnd = Carbon::parse($payload['week_end'])->startOfDay();

        return DB::transaction(function () use ($payload, $weekStart, $weekEnd, $replaceExisting) {
            $week = PlanningWeek::query()
                ->whereDate('week_start', $weekStart)
                ->whereDate('week_end', $weekEnd)
                ->first();

            if ($week === null) {
                $week = PlanningWeek::create([
                    'label' => $this->buildWeekLabel($weekStart, $weekEnd),
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'is_current' => false,
                    'campus' => $payload['campus'] ?? null,
                    'schedule_reference' => $payload['reference'] ?? null,
                ]);
            } else {
                $week->update([
                    'campus' => $payload['campus'] ?? $week->campus,
                    'schedule_reference' => $payload['reference'] ?? $week->schedule_reference,
                ]);
            }

            if ($replaceExisting) {
                $week->events()->delete();
            }

            $created = 0;
            $skipped = 0;

            foreach ($payload['items'] as $item) {
                if ($this->eventExists($week, $item)) {
                    $skipped++;

                    continue;
                }

                $this->createEventFromScheduleItem($week, $item);
                $created++;
            }

            return [
                'week' => $week->fresh(),
                'events_created' => $created,
                'events_skipped' => $skipped,
            ];
        });
    }

    /**
     * @return array{week: PlanningWeek, events_created: int, events_skipped: int}
     */
    public function importFromJsonFile(string $path, bool $replaceExisting = false): array
    {
        if (! is_readable($path)) {
            throw new InvalidArgumentException("Schedule file not readable: {$path}");
        }

        $decoded = json_decode(file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Invalid weekly schedule JSON file.');
        }

        return $this->importFromArray($decoded, $replaceExisting);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validatePayload(array $payload): void
    {
        foreach (['week_start', 'week_end', 'items'] as $key) {
            if (! array_key_exists($key, $payload)) {
                throw new InvalidArgumentException("Weekly schedule missing required field: {$key}");
            }
        }

        if (! is_array($payload['items']) || $payload['items'] === []) {
            throw new InvalidArgumentException('Weekly schedule must include at least one programme item.');
        }
    }

    private function buildWeekLabel(Carbon $weekStart, Carbon $weekEnd): string
    {
        if ($weekStart->isSameDay($weekEnd)) {
            return 'Week of '.$weekStart->format('d M Y');
        }

        return sprintf(
            'Week of %s – %s %s',
            $weekStart->format('d M'),
            $weekEnd->format('d'),
            $weekEnd->format('Y'),
        );
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function eventExists(PlanningWeek $week, array $item): bool
    {
        $query = $week->events()
            ->where('name', $item['programme'])
            ->whereDate('event_date', $item['date_start']);

        if (! empty($item['schedule_reference'])) {
            $query->where('schedule_reference', $item['schedule_reference']);
        }

        return $query->exists();
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function createEventFromScheduleItem(PlanningWeek $week, array $item): Event
    {
        $category = ScheduleCategory::from($item['category']);
        $dateStart = Carbon::parse($item['date_start']);
        $dateEnd = Carbon::parse($item['date_end'] ?? $item['date_start']);

        $participantsResidential = $item['participants_residential'] ?? null;
        $participantsNonResidential = $item['participants_non_residential'] ?? null;
        $totalParticipants = null;

        if ($participantsResidential !== null || $participantsNonResidential !== null) {
            $totalParticipants = (int) ($participantsResidential ?? 0) + (int) ($participantsNonResidential ?? 0);
        }

        $description = collect([
            $item['client'] ? "Client: {$item['client']}" : null,
            $totalParticipants ? "Participants: {$totalParticipants}" : null,
            $item['remarks'] ? "Remarks: {$item['remarks']}" : null,
        ])->filter()->implode("\n");

        return Event::create([
            'planning_week_id' => $week->id,
            'name' => $item['programme'],
            'type' => $category->eventType(),
            'schedule_category' => $category->value,
            'client' => $item['client'] ?? null,
            'event_date' => $dateStart,
            'event_date_end' => $dateEnd,
            'event_time' => null,
            'venue' => $item['venue'] ?? null,
            'participants_residential' => $participantsResidential,
            'participants_non_residential' => $participantsNonResidential,
            'coordinator' => $item['coordinator'] ?? null,
            'programme_assistant' => $item['programme_assistant'] ?? null,
            'package_remarks' => $item['remarks'] ?? null,
            'schedule_reference' => $item['schedule_reference'] ?? null,
            'description' => $description !== '' ? $description : null,
        ]);
    }
}
