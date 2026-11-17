{{-- Step 1: Organization & Contact Information --}}

<div class="grid md:grid-cols-2 gap-6">
    {{-- Organization Name --}}
    <div class="md:col-span-2">
        <label for="organization_name" class="block text-sm font-medium text-gray-700 mb-1">
            Organization Name <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="organization_name" 
               name="organization_name" 
               value="{{ old('organization_name', session('booking_step_1.organization_name')) }}"
               required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- Organization Category --}}
    <div>
        <label for="organization_category" class="block text-sm font-medium text-gray-700 mb-1">
            Organization Category <span class="text-red-500">*</span>
        </label>
        <select id="organization_category" 
                name="organization_category" 
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Select category</option>
            @foreach($organizationCategories as $key => $label)
                <option value="{{ $key }}" {{ old('organization_category', session('booking_step_1.organization_category')) === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Country --}}
    <div>
        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
            Country <span class="text-red-500">*</span>
        </label>
        <select id="country" 
                name="country" 
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            @foreach($countries as $key => $label)
                <option value="{{ $label }}" {{ old('country', session('booking_step_1.country', 'Kenya')) === $label ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Contact Person Name --}}
    <div>
        <label for="contact_person_name" class="block text-sm font-medium text-gray-700 mb-1">
            Contact Person Full Name <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="contact_person_name" 
               name="contact_person_name" 
               value="{{ old('contact_person_name', session('booking_step_1.contact_person_name')) }}"
               required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- Designation --}}
    <div>
        <label for="contact_person_designation" class="block text-sm font-medium text-gray-700 mb-1">
            Designation / Job Title
        </label>
        <input type="text" 
               id="contact_person_designation" 
               name="contact_person_designation" 
               value="{{ old('contact_person_designation', session('booking_step_1.contact_person_designation')) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- Email --}}
    <div>
        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">
            Email Address <span class="text-red-500">*</span>
        </label>
        <input type="email" 
               id="contact_email" 
               name="contact_email" 
               value="{{ old('contact_email', session('booking_step_1.contact_email')) }}"
               required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    {{-- Primary Phone --}}
    <div>
        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">
            Primary Phone Number <span class="text-red-500">*</span>
        </label>
        <input type="tel" 
               id="contact_phone" 
               name="contact_phone" 
               value="{{ old('contact_phone', session('booking_step_1.contact_phone')) }}"
               required
               placeholder="+254..."
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>
</div>
