<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanningWeekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'financial_year' => ['nullable', 'string', 'max:20'],
            'week_number' => ['nullable', 'integer', 'min:1', 'max:52'],
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'is_current' => ['sometimes', 'boolean'],
            'campus' => ['nullable', 'string', 'max:100'],
            'schedule_reference' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_current' => $this->boolean('is_current'),
        ]);

        // Auto-calculate FY and week number if not provided
        if (! $this->filled('financial_year') && $this->filled('week_start')) {
            $weekStart = Carbon::parse($this->input('week_start'));
            $fyStartMonth = 7; // July
            $year = $weekStart->year;

            if ($weekStart->month < $fyStartMonth) {
                $fyStart = Carbon::create($year - 1, $fyStartMonth, 1);
                $fy = ($year - 1).'/'.$year;
            } else {
                $fyStart = Carbon::create($year, $fyStartMonth, 1);
                $fy = $year.'/'.($year + 1);
            }

            $weekNumber = (int) ceil($weekStart->diffInDays($fyStart->startOfWeek()) / 7) + 1;
            $weekNumber = min(max($weekNumber, 1), 52);

            $this->merge([
                'financial_year' => $fy,
                'week_number' => $weekNumber,
            ]);
        }
    }
}
