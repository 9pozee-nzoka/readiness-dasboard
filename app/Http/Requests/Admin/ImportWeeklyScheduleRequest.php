<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportWeeklyScheduleRequest extends FormRequest
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
            'schedule_file' => ['nullable', 'file', 'mimes:json,txt', 'max:5120'],
            'sample_schedule' => ['nullable', 'string', 'in:2026-05-18'],
            'replace_existing' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'replace_existing' => $this->boolean('replace_existing'),
        ]);
    }
}
