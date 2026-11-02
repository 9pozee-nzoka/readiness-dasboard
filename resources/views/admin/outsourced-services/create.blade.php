<x-layouts.admin>
    <x-slot:title>Record New Outsourced Service</x-slot:title>

    <div class="max-w-3xl">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">New External Service / Hiring</h2>
                <p class="text-sm text-gray-500 mt-1">Record an outsourced service or external hiring for event management.</p>
            </div>

            <form method="POST" action="{{ route('admin.outsourced-services.store') }}" class="p-6 space-y-5">
                @csrf

                {{-- Event --}}
                <div>
                    <label for="event_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Event <span class="text-red-500">*</span>
                    </label>
                    <select name="event_id" id="event_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('event_id') border-red-300 @enderror">
                        <option value="">Select event...</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected(old('event_id', $selectedEventId) == $event->id)>
                                {{ $event->name }} ({{ $event->event_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('event_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Department --}}
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Requesting Department <span class="text-red-500">*</span>
                    </label>
                    <select name="department_id" id="department_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('department_id') border-red-300 @enderror">
                        <option value="">Select department...</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Responsible User --}}
                <div>
                    <label for="responsible_user_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Responsible Person <span class="text-red-500">*</span>
                    </label>
                    <select name="responsible_user_id" id="responsible_user_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('responsible_user_id') border-red-300 @enderror">
                        <option value="">Select responsible person...</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" 
                                data-department="{{ $user->department_id }}"
                                @selected(old('responsible_user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->department?->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('responsible_user_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const deptSelect = document.getElementById('department_id');
                        const userSelect = document.getElementById('responsible_user_id');
                        const allOptions = Array.from(userSelect.querySelectorAll('option[data-department]'));

                        function filterUsers() {
                            const selectedDept = deptSelect.value;
                            
                            // Reset and show placeholder
                            userSelect.value = '';
                            
                            // Show/hide options based on selected department
                            allOptions.forEach(option => {
                                if (!selectedDept || option.dataset.department === selectedDept) {
                                    option.style.display = '';
                                } else {
                                    option.style.display = 'none';
                                }
                            });
                        }

                        deptSelect.addEventListener('change', filterUsers);
                        
                        // Initial filter on page load
                        if (deptSelect.value) {
                            filterUsers();
                        }
                    });
                </script>

                {{-- Service Type --}}
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors @error('type') border-red-300 @else border-gray-300 @enderror">
                            <input type="radio" name="type" value="outsourced_service" required
                                @checked(old('type', 'outsourced_service') == 'outsourced_service')
                                class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Outsourced Service</span>
                                <span class="block text-xs text-gray-500">Equipment, venue, catering, etc.</span>
                            </span>
                        </label>
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors @error('type') border-red-300 @else border-gray-300 @enderror">
                            <input type="radio" name="type" value="hiring" required
                                @checked(old('type') == 'hiring')
                                class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">External Hiring</span>
                                <span class="block text-xs text-gray-500">Consultants, experts, facilitators</span>
                            </span>
                        </label>
                    </div>
                    @error('type')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Provider --}}
                <div>
                    <label for="provider" class="block text-sm font-medium text-gray-700 mb-1">
                        Provider / Service Company <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="provider" id="provider" required
                        value="{{ old('provider') }}"
                        placeholder="e.g., Elite Security Services Ltd"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('provider') border-red-300 @enderror">
                    @error('provider')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Service Type Description --}}
                <div>
                    <label for="service_type" class="block text-sm font-medium text-gray-700 mb-1">
                        Service Type <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="service_type" id="service_type" required
                        value="{{ old('service_type') }}"
                        placeholder="e.g., Additional Security Personnel, AV Equipment Rental"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('service_type') border-red-300 @enderror">
                    @error('service_type')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Reason --}}
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Reason / Justification <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" id="reason" rows="3" required
                        placeholder="Explain why external service is needed (e.g., internal capacity insufficient, specialized expertise required)"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('reason') border-red-300 @enderror">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="start_date" id="start_date" required
                            value="{{ old('start_date') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 @enderror">
                        @error('start_date')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                            End Date
                        </label>
                        <input type="date" name="end_date" id="end_date"
                            value="{{ old('end_date') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-300 @enderror">
                        @error('end_date')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Cost --}}
                <div>
                    <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">
                        Estimated / Approved Cost (KSh)
                    </label>
                    <input type="number" name="cost" id="cost" step="0.01" min="0"
                        value="{{ old('cost') }}"
                        placeholder="0.00"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('cost') border-red-300 @enderror">
                    @error('cost')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Additional Notes
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                        placeholder="Any additional details, specifications, or requirements"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.outsourced-services.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                        Record Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
