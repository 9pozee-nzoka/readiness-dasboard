<x-layouts.admin title="Convert Booking to Event">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('admin.bookings.show', $booking) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Booking
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Convert Booking to Internal Event</h1>
            <p class="text-gray-600 mt-1">Review requirements and assign departments</p>
        </div>

        {{-- Booking Summary --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Booking Summary</h2>
            <dl class="grid sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Reference</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $booking->booking_reference }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Event Title</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->event_title }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Organization</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $booking->organization_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Event Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $booking->preferred_start_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Participants</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $booking->expected_participants }} people</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Venue</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $booking->preferred_venue ?? 'TBD' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Requirements Form --}}
        <form method="POST" action="{{ route('admin.bookings.process-conversion', $booking) }}">
            @csrf

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Requirements & Department Assignment</h2>
                <p class="text-sm text-gray-600 mb-6">Review the auto-generated requirements and assign the responsible departments. You can edit descriptions or add/remove requirements as needed.</p>

                <div id="requirements-list" class="space-y-4">
                    @foreach($requirements as $index => $requirement)
                        <div class="requirement-item p-4 border border-gray-200 rounded-lg" data-index="{{ $index }}">
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Requirement Description *
                                    </label>
                                    <textarea 
                                        name="requirements[{{ $index }}][description]" 
                                        rows="2" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >{{ $requirement['description'] }}</textarea>
                                </div>
                                <div class="w-48">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Assign Department *
                                    </label>
                                    <select 
                                        name="requirements[{{ $index }}][department_id]" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="">Select...</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ $requirement['suggested_department_id'] == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Priority
                                    </label>
                                    <select 
                                        name="requirements[{{ $index }}][priority]" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="button" onclick="removeRequirement(this)" class="px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addRequirement()" class="mt-4 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Another Requirement
                </button>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.bookings.show', $booking) }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                    Convert to Event
                </button>
            </div>
        </form>
    </div>

    <script>
        let requirementIndex = {{ count($requirements) }};

        function addRequirement() {
            const container = document.getElementById('requirements-list');
            const html = `
                <div class="requirement-item p-4 border border-gray-200 rounded-lg" data-index="${requirementIndex}">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Requirement Description *
                            </label>
                            <textarea 
                                name="requirements[${requirementIndex}][description]" 
                                rows="2" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Describe the requirement..."
                            ></textarea>
                        </div>
                        <div class="w-48">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Assign Department *
                            </label>
                            <select 
                                name="requirements[${requirementIndex}][department_id]" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">Select...</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Priority
                            </label>
                            <select 
                                name="requirements[${requirementIndex}][priority]" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="removeRequirement(this)" class="px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            requirementIndex++;
        }

        function removeRequirement(button) {
            const item = button.closest('.requirement-item');
            const container = document.getElementById('requirements-list');
            
            // Prevent removing the last requirement
            if (container.children.length <= 1) {
                alert('At least one requirement is needed.');
                return;
            }
            
            item.remove();
        }
    </script>

</x-layouts.admin>
