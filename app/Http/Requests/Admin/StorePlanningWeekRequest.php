<?php

namespace App\Http\Requests\Admin;

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
    }
}
