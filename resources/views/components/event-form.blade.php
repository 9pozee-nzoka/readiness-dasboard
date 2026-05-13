@props(['weeks', 'event' => null])

{{-- Planning Week --}}
<div>
    <label for="planning_week_id" class="block text-sm font-medium text-gray-700 mb-1">Planning Week <span class="text-red-500">*</span></label>
    <select name="planning_week_id" id="planning_week_id"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('planning_week_id') border-red-400 @enderror">
        <option value="">Select a week…</option>
        @foreach ($weeks as $week)
            <option value="{{ $week->id }}"
                @selected(old('planning_week_id', $event?->planning_week_id) == $week->id)>
                {{ $week->label }}{{ $week->is_current ? ' (Current)' : '' }}
            </option>
        @endforeach
    </select>
    @error('planning_week_id')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Event Name --}}
<div>
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Event Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" id="name"
        value="{{ old('name', $event?->name) }}"
        placeholder="e.g. Annual General Meeting 2026"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
    @error('name')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Type --}}
<div>
    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Event Type <span class="text-red-500">*</span></label>
    <input type="text" name="type" id="type"
        value="{{ old('type', $event?->type) }}"
        placeholder="e.g. Meeting, Ceremony, Training"
        list="event-types"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('type') border-red-400 @enderror">
    <datalist id="event-types">
        <option value="Meeting">
        <option value="Training">
        <option value="Ceremony">
        <option value="Forum">
        <option value="Review">
        <option value="Retreat">
        <option value="Workshop">
    </datalist>
    @error('type')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Date & Time --}}
<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="event_date" class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
        <input type="date" name="event_date" id="event_date"
            value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}"
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('event_date') border-red-400 @enderror">
        @error('event_date')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="event_time" class="block text-sm font-medium text-gray-700 mb-1">Time</label>
        <input type="time" name="event_time" id="event_time"
            value="{{ old('event_time', $event?->event_time) }}"
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
    </div>
</div>

{{-- Venue --}}
<div>
    <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
    <input type="text" name="venue" id="venue"
        value="{{ old('venue', $event?->venue) }}"
        placeholder="e.g. Main Boardroom"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
</div>

{{-- Description --}}
<div>
    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
    <textarea name="description" id="description" rows="3"
        placeholder="Optional notes about this event…"
        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('description', $event?->description) }}</textarea>
</div>
