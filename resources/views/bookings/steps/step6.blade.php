{{-- Step 6: Catering & Hospitality --}}

<div class="space-y-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            Specify your meal and hospitality requirements. Our catering team will prepare a detailed quotation based on your selections.
        </p>
    </div>

    {{-- Meals Selection --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-4">Meal Services Required</h3>
        
        <div class="space-y-3">
            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="catering[breakfast]" 
                           value="1"
                           {{ old('catering.breakfast', session('booking_step_6.catering.breakfast')) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-3 text-gray-700 font-medium">Breakfast</span>
                </div>
                <span class="text-sm text-gray-500">Continental or Full Breakfast</span>
            </label>

            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="catering[morning_tea]" 
                           value="1"
                           {{ old('catering.morning_tea', session('booking_step_6.catering.morning_tea')) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-3 text-gray-700 font-medium">Morning Tea / Coffee Break</span>
                </div>
                <span class="text-sm text-gray-500">Tea, coffee, snacks</span>
            </label>

            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="catering[lunch]" 
                           value="1"
                           {{ old('catering.lunch', session('booking_step_6.catering.lunch')) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-3 text-gray-700 font-medium">Lunch</span>
                </div>
                <span class="text-sm text-gray-500">Full meal service</span>
            </label>

            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="catering[afternoon_tea]" 
                           value="1"
                           {{ old('catering.afternoon_tea', session('booking_step_6.catering.afternoon_tea')) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-3 text-gray-700 font-medium">Afternoon Tea / Coffee Break</span>
                </div>
                <span class="text-sm text-gray-500">Tea, coffee, snacks</span>
            </label>

            <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="catering[dinner]" 
                           value="1"
                           {{ old('catering.dinner', session('booking_step_6.catering.dinner')) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-3 text-gray-700 font-medium">Dinner</span>
                </div>
                <span class="text-sm text-gray-500">Evening meal</span>
            </label>
        </div>
    </div>

    {{-- Number of Guests --}}
    <div>
        <label for="catering_guests" class="block text-sm font-medium text-gray-700 mb-1">
            Number of Guests per Meal <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               id="catering_guests" 
               name="catering[guests_per_meal]" 
               value="{{ old('catering.guests_per_meal', session('booking_step_6.catering.guests_per_meal')) }}"
               min="1"
               required
               placeholder="Enter expected number of guests"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <p class="text-xs text-gray-500 mt-1">This will be used to calculate catering costs</p>
    </div>

    {{-- Dietary Requirements --}}
    <div>
        <label for="catering_dietary" class="block text-sm font-medium text-gray-700 mb-1">
            Special Dietary Requirements
        </label>
        <textarea id="catering_dietary" 
                  name="catering[dietary_requirements]" 
                  rows="3"
                  placeholder="e.g., Number of vegetarian meals, halal requirements, allergies, gluten-free options..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('catering.dietary_requirements', session('booking_step_6.catering.dietary_requirements')) }}</textarea>
    </div>

    {{-- VIP Arrangements --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-4">VIP & Special Arrangements</h3>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">
                VIP Dining Arrangements Required?
            </label>
            <div class="flex gap-6">
                <label class="flex items-center">
                    <input type="radio" 
                           name="catering[vip_dining]" 
                           value="1" 
                           {{ old('catering.vip_dining', session('booking_step_6.catering.vip_dining')) == '1' ? 'checked' : '' }}
                           onclick="document.getElementById('vip_details_field').classList.remove('hidden')"
                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-700">Yes</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" 
                           name="catering[vip_dining]" 
                           value="0" 
                           {{ old('catering.vip_dining', session('booking_step_6.catering.vip_dining', '0')) == '0' ? 'checked' : '' }}
                           onclick="document.getElementById('vip_details_field').classList.add('hidden')"
                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-700">No</span>
                </label>
            </div>
        </div>

        <div id="vip_details_field" class="{{ old('catering.vip_dining', session('booking_step_6.catering.vip_dining')) == '1' ? '' : 'hidden' }}">
            <label for="vip_details" class="block text-sm font-medium text-gray-700 mb-1">
                VIP Details
            </label>
            <input type="number" 
                   id="vip_count" 
                   name="catering[vip_count]" 
                   value="{{ old('catering.vip_count', session('booking_step_6.catering.vip_count')) }}"
                   min="0"
                   placeholder="Number of VIP guests"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 mb-3">
            <textarea id="vip_special_requests" 
                      name="catering[vip_special_requests]" 
                      rows="2"
                      placeholder="Special requirements for VIP dining..."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('catering.vip_special_requests', session('booking_step_6.catering.vip_special_requests')) }}</textarea>
        </div>
    </div>

    {{-- Additional Hospitality Requests --}}
    <div>
        <label for="hospitality_requests" class="block text-sm font-medium text-gray-700 mb-1">
            Additional Hospitality Requests
        </label>
        <textarea id="hospitality_requests" 
                  name="catering[hospitality_requests]" 
                  rows="3"
                  placeholder="Any other special hospitality requirements or requests..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('catering.hospitality_requests', session('booking_step_6.catering.hospitality_requests')) }}</textarea>
    </div>

    {{-- Final Note --}}
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="font-semibold text-green-900 mb-2">Almost Done!</h3>
        <p class="text-sm text-green-800">
            After submitting, you'll receive a booking confirmation email with your booking reference number. 
            Our team will review your request and send you a detailed quotation within 2-3 business days.
        </p>
    </div>
</div>
