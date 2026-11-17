{{-- Step 4: Venue & Accommodation --}}

<div class="space-y-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            <strong>Note:</strong> Our team will allocate the most suitable venue based on your participant count and requirements. You'll be notified of the venue details upon booking confirmation.
        </p>
    </div>

    {{-- Parking Requirements --}}
    <div>
        <label for="parking_spaces" class="block text-sm font-medium text-gray-700 mb-1">
            Estimated Parking Spaces Required
        </label>
        <input type="number" 
               id="parking_spaces" 
               name="parking_spaces" 
               value="{{ old('parking_spaces', session('booking_step_4.parking_spaces')) }}"
               min="0"
               placeholder="Number of vehicles"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- Accommodation Section --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-4">Accommodation Requirements</h3>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">
                Do you require accommodation? <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-6">
                <label class="flex items-center">
                    <input type="radio" 
                           name="accommodation_required" 
                           value="1" 
                           {{ old('accommodation_required', session('booking_step_4.accommodation_required')) == '1' ? 'checked' : '' }}
                           onclick="document.getElementById('accommodation_fields').classList.remove('hidden')"
                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-700">Yes</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" 
                           name="accommodation_required" 
                           value="0" 
                           {{ old('accommodation_required', session('booking_step_4.accommodation_required', '0')) == '0' ? 'checked' : '' }}
                           onclick="document.getElementById('accommodation_fields').classList.add('hidden')"
                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-700">No</span>
                </label>
            </div>
        </div>

        <div id="accommodation_fields" class="grid md:grid-cols-2 gap-6 {{ old('accommodation_required', session('booking_step_4.accommodation_required')) == '1' ? '' : 'hidden' }}">
            <div>
                <label for="accommodation_rooms" class="block text-sm font-medium text-gray-700 mb-1">
                    Number of Rooms
                </label>
                <input type="number" 
                       id="accommodation_rooms" 
                       name="accommodation_rooms" 
                       value="{{ old('accommodation_rooms', session('booking_step_4.accommodation_rooms')) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="accommodation_nights" class="block text-sm font-medium text-gray-700 mb-1">
                    Number of Nights
                </label>
                <input type="number" 
                       id="accommodation_nights" 
                       name="accommodation_nights" 
                       value="{{ old('accommodation_nights', session('booking_step_4.accommodation_nights')) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
    </div>

    {{-- Catering Section --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-4">Catering Requirements</h3>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">
                Do you require catering services? <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-6">
                <label class="flex items-center">
                    <input type="radio" 
                           name="catering_required" 
                           value="1" 
                           {{ old('catering_required', session('booking_step_4.catering_required')) == '1' ? 'checked' : '' }}
                           onclick="document.getElementById('catering_fields').classList.remove('hidden')"
                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-700">Yes</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" 
                           name="catering_required" 
                           value="0" 
                           {{ old('catering_required', session('booking_step_4.catering_required', '0')) == '0' ? 'checked' : '' }}
                           onclick="document.getElementById('catering_fields').classList.add('hidden')"
                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-700">No</span>
                </label>
            </div>
        </div>

        <div id="catering_fields" class="space-y-4 {{ old('catering_required', session('booking_step_4.catering_required')) == '1' ? '' : 'hidden' }}">
            <div>
                <label for="catering_package" class="block text-sm font-medium text-gray-700 mb-1">
                    Catering Package
                </label>
                <select id="catering_package" 
                        name="catering_package"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select package</option>
                    @foreach($cateringPackages as $key => $label)
                        <option value="{{ $key }}" {{ old('catering_package', session('booking_step_4.catering_package')) === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="special_dietary_requirements" class="block text-sm font-medium text-gray-700 mb-1">
                    Special Dietary Requirements
                </label>
                <textarea id="special_dietary_requirements" 
                          name="special_dietary_requirements" 
                          rows="2"
                          placeholder="e.g., Vegetarian, Halal, Gluten-free, Allergies"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('special_dietary_requirements', session('booking_step_4.special_dietary_requirements')) }}</textarea>
            </div>
        </div>
    </div>
</div>
