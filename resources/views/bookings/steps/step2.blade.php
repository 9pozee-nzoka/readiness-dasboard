{{-- Step 2: Event Information --}}

<div class="space-y-6">
    {{-- Event Title --}}
    <div>
        <label for="event_title" class="block text-sm font-medium text-gray-700 mb-1">
            Event Title <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="event_title" 
               name="event_title" 
               value="{{ old('event_title', session('booking_step_2.event_title')) }}"
               required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Event Category --}}
        <div>
            <label for="event_category" class="block text-sm font-medium text-gray-700 mb-1">
                Event Category <span class="text-red-500">*</span>
            </label>
            <select id="event_category" 
                    name="event_category" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select category</option>
                @foreach($eventCategories as $key => $label)
                    <option value="{{ $key }}" {{ old('event_category', session('booking_step_2.event_category')) === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Expected Participants --}}
        <div>
            <label for="expected_participants" class="block text-sm font-medium text-gray-700 mb-1">
                Expected Number of Participants <span class="text-red-500">*</span>
            </label>
            <input type="number" 
                   id="expected_participants" 
                   name="expected_participants" 
                   value="{{ old('expected_participants', session('booking_step_2.expected_participants')) }}"
                   min="1"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>
</div>
