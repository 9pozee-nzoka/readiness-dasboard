@php $week = $week ?? null; @endphp

<div>
    <label for="label" class="block text-sm font-medium text-gray-700 mb-1">Label <span class="text-red-500">*</span></label>
    <input type="text" name="label" id="label" value="{{ old('label', $week?->label) }}" required
        placeholder="Week of 18 – 22 May 2026"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
    @error('label') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="week_start" class="block text-sm font-medium text-gray-700 mb-1">Week start <span class="text-red-500">*</span></label>
        <input type="date" name="week_start" id="week_start" value="{{ old('week_start', $week?->week_start?->format('Y-m-d')) }}" required
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        @error('week_start') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="week_end" class="block text-sm font-medium text-gray-700 mb-1">Week end <span class="text-red-500">*</span></label>
        <input type="date" name="week_end" id="week_end" value="{{ old('week_end', $week?->week_end?->format('Y-m-d')) }}" required
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        @error('week_end') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label for="campus" class="block text-sm font-medium text-gray-700 mb-1">Campus</label>
    <input type="text" name="campus" id="campus" value="{{ old('campus', $week?->campus) }}" placeholder="Nairobi"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
</div>

<div>
    <label for="schedule_reference" class="block text-sm font-medium text-gray-700 mb-1">Schedule reference</label>
    <input type="text" name="schedule_reference" id="schedule_reference" value="{{ old('schedule_reference', $week?->schedule_reference) }}" placeholder="KSG/12/WS/01"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
</div>

<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_current" value="1" @checked(old('is_current', $week?->is_current))
        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
    Mark as current planning week
</label>
