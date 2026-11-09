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

<div class="grid grid-cols-2 gap-4 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
    <div>
        <label class="block text-sm font-medium text-indigo-900 mb-1">Financial Year</label>
        <p id="fy-display" class="text-lg font-semibold text-indigo-700">
            {{ old('financial_year', $week?->financial_year) ?: '—' }}
        </p>
        <p class="text-xs text-indigo-600 mt-1">Auto-calculated (July-June cycle)</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-indigo-900 mb-1">Week Number</label>
        <p id="week-num-display" class="text-lg font-semibold text-indigo-700">
            {{ old('week_number', $week?->week_number) ? 'Week '.old('week_number', $week?->week_number) : '—' }}
        </p>
        <p class="text-xs text-indigo-600 mt-1">Week 1-52 within FY</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const weekStartInput = document.getElementById('week_start');
    const fyDisplay = document.getElementById('fy-display');
    const weekNumDisplay = document.getElementById('week-num-display');

    function calculateFYAndWeek() {
        const weekStartValue = weekStartInput.value;
        if (!weekStartValue) {
            fyDisplay.textContent = '—';
            weekNumDisplay.textContent = '—';
            return;
        }

        const weekStart = new Date(weekStartValue);
        const year = weekStart.getFullYear();
        const month = weekStart.getMonth() + 1; // JS months are 0-indexed
        const fyStartMonth = 7; // July

        let fyStart, fy;
        if (month < fyStartMonth) {
            fyStart = new Date(year - 1, fyStartMonth - 1, 1);
            fy = (year - 1) + '/' + year;
        } else {
            fyStart = new Date(year, fyStartMonth - 1, 1);
            fy = year + '/' + (year + 1);
        }

        // Calculate week number
        const diffTime = weekStart - fyStart;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        let weekNumber = Math.floor(diffDays / 7) + 1;
        weekNumber = Math.min(Math.max(weekNumber, 1), 52);

        fyDisplay.textContent = fy;
        weekNumDisplay.textContent = 'Week ' + weekNumber;
    }

    weekStartInput.addEventListener('change', calculateFYAndWeek);
    
    // Calculate on page load if week_start is already filled
    if (weekStartInput.value) {
        calculateFYAndWeek();
    }
});
</script>
