{{-- Step 5: Equipment & Technical Requirements --}}

<div class="space-y-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            Select all the equipment and technical services you'll need for your event. We'll include these in your quotation.
        </p>
    </div>

    {{-- Audio Visual Equipment --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-4">Audio Visual Equipment</h3>
        
        <div class="grid md:grid-cols-2 gap-4">
            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[projector]" 
                       value="1"
                       {{ old('equipment.projector', session('booking_step_5.equipment.projector')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Projector & Screen</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[pa_system]" 
                       value="1"
                       {{ old('equipment.pa_system', session('booking_step_5.equipment.pa_system')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Public Address System</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[video_conferencing]" 
                       value="1"
                       {{ old('equipment.video_conferencing', session('booking_step_5.equipment.video_conferencing')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Video Conferencing Facilities</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[wifi]" 
                       value="1"
                       {{ old('equipment.wifi', session('booking_step_5.equipment.wifi')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Internet / Wi-Fi</span>
            </label>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="wireless_microphones" class="block text-sm font-medium text-gray-700 mb-1">
                    Wireless Microphones (Quantity)
                </label>
                <input type="number" 
                       id="wireless_microphones" 
                       name="equipment[wireless_microphones]" 
                       value="{{ old('equipment.wireless_microphones', session('booking_step_5.equipment.wireless_microphones')) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="wired_microphones" class="block text-sm font-medium text-gray-700 mb-1">
                    Wired Microphones (Quantity)
                </label>
                <input type="number" 
                       id="wired_microphones" 
                       name="equipment[wired_microphones]" 
                       value="{{ old('equipment.wired_microphones', session('booking_step_5.equipment.wired_microphones')) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
    </div>

    {{-- Computing Equipment --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-4">Computing Equipment</h3>
        
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="computers" class="block text-sm font-medium text-gray-700 mb-1">
                    Computers / Laptops (Quantity)
                </label>
                <input type="number" 
                       id="computers" 
                       name="equipment[computers]" 
                       value="{{ old('equipment.computers', session('booking_step_5.equipment.computers')) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[printing]" 
                       value="1"
                       {{ old('equipment.printing', session('booking_step_5.equipment.printing')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Printing & Photocopying Services</span>
            </label>
        </div>
    </div>

    {{-- Documentation Services --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 mb-4">Documentation Services</h3>
        
        <div class="grid md:grid-cols-2 gap-4">
            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[video_recording]" 
                       value="1"
                       {{ old('equipment.video_recording', session('booking_step_5.equipment.video_recording')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Video Recording</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[photography]" 
                       value="1"
                       {{ old('equipment.photography', session('booking_step_5.equipment.photography')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Professional Photography</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[live_streaming]" 
                       value="1"
                       {{ old('equipment.live_streaming', session('booking_step_5.equipment.live_streaming')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Live Streaming</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" 
                       name="equipment[interpretation]" 
                       value="1"
                       {{ old('equipment.interpretation', session('booking_step_5.equipment.interpretation')) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-3 text-gray-700">Interpretation Facilities</span>
            </label>
        </div>
    </div>

    {{-- Other Requirements --}}
    <div>
        <label for="equipment_other" class="block text-sm font-medium text-gray-700 mb-1">
            Other Equipment or Technical Requirements
        </label>
        <textarea id="equipment_other" 
                  name="equipment[other]" 
                  rows="3"
                  placeholder="Please specify any additional equipment or technical support you need..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('equipment.other', session('booking_step_5.equipment.other')) }}</textarea>
    </div>
</div>
