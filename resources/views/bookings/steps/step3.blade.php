{{-- Step 3: Event Dates & Scheduling --}}

<div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-6">
        {{-- Preferred Start Date --}}
        <div>
            <label for="preferred_start_date" class="block text-sm font-medium text-gray-700 mb-1">
                Preferred Start Date <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="preferred_start_date" 
                   name="preferred_start_date" 
                   value="{{ old('preferred_start_date', session('booking_step_3.preferred_start_date')) }}"
                   min="{{ date('Y-m-d') }}"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- Preferred End Date --}}
        <div>
            <label for="preferred_end_date" class="block text-sm font-medium text-gray-700 mb-1">
                Preferred End Date <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="preferred_end_date" 
                   name="preferred_end_date" 
                   value="{{ old('preferred_end_date', session('booking_step_3.preferred_end_date')) }}"
                   min="{{ date('Y-m-d') }}"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Daily Start Time --}}
        <div>
            <label for="daily_start_time" class="block text-sm font-medium text-gray-700 mb-1">
                Daily Start Time <span class="text-red-500">*</span>
            </label>
            <input type="time" 
                   id="daily_start_time" 
                   name="daily_start_time" 
                   value="{{ old('daily_start_time', session('booking_step_3.daily_start_time')) }}"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- Daily End Time --}}
        <div>
            <label for="daily_end_time" class="block text-sm font-medium text-gray-700 mb-1">
                Daily End Time <span class="text-red-500">*</span>
            </label>
            <input type="time" 
                   id="daily_end_time" 
                   name="daily_end_time" 
                   value="{{ old('daily_end_time', session('booking_step_3.daily_end_time')) }}"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>
</div>
