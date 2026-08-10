@props(['event' => null, 'scheduleCategories' => []])

<div class="border-t border-gray-100 pt-5 mt-5">
    <h3 class="text-sm font-semibold text-gray-900 mb-4">Weekly schedule details (KSG format)</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="schedule_category" class="block text-sm font-medium text-gray-700 mb-1">Schedule section</label>
            <select name="schedule_category" id="schedule_category"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <option value="">—</option>
                @foreach ($scheduleCategories as $value => $label)
                    <option value="{{ $value }}" @selected(old('schedule_category', $event?->schedule_category?->value) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="schedule_reference" class="block text-sm font-medium text-gray-700 mb-1">Reference (e.g. No.544/2026)</label>
            <input type="text" name="schedule_reference" id="schedule_reference"
                value="{{ old('schedule_reference', $event?->schedule_reference) }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div class="sm:col-span-2">
            <label for="client" class="block text-sm font-medium text-gray-700 mb-1">Client / programme owner</label>
            <input type="text" name="client" id="client" value="{{ old('client', $event?->client) }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label for="event_date_end" class="block text-sm font-medium text-gray-700 mb-1">End date</label>
            <input type="date" name="event_date_end" id="event_date_end"
                value="{{ old('event_date_end', $event?->event_date_end?->format('Y-m-d')) }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label for="package_remarks" class="block text-sm font-medium text-gray-700 mb-1">Package / remarks</label>
            <input type="text" name="package_remarks" id="package_remarks"
                value="{{ old('package_remarks', $event?->package_remarks) }}"
                placeholder="Executive, Deluxe, Online…"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label for="participants_residential" class="block text-sm font-medium text-gray-700 mb-1">Residential participants</label>
            <input type="number" min="0" name="participants_residential" id="participants_residential"
                value="{{ old('participants_residential', $event?->participants_residential) }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label for="participants_non_residential" class="block text-sm font-medium text-gray-700 mb-1">Non-residential participants</label>
            <input type="number" min="0" name="participants_non_residential" id="participants_non_residential"
                value="{{ old('participants_non_residential', $event?->participants_non_residential) }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label for="coordinator" class="block text-sm font-medium text-gray-700 mb-1">Course coordinator</label>
            <input type="text" name="coordinator" id="coordinator" value="{{ old('coordinator', $event?->coordinator) }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div>
            <label for="programme_assistant" class="block text-sm font-medium text-gray-700 mb-1">Programme assistant</label>
            <input type="text" name="programme_assistant" id="programme_assistant"
                value="{{ old('programme_assistant', $event?->programme_assistant) }}"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
    </div>
</div>
