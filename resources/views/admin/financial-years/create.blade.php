<x-layouts.admin>
    <x-slot:title>New Financial Year</x-slot:title>

    <div class="max-w-2xl">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Create Financial Year</h2>
            <p class="text-sm text-gray-500">KSG financial year runs from July 1 to June 30</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.financial-years.store') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Financial Year Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        placeholder="e.g. 2027/2028"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name') 
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Format: YYYY/YYYY (e.g., 2027/2028)</p>
                </div>

                {{-- Start Date --}}
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('start_date') border-red-400 @enderror">
                    @error('start_date') 
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">KSG FY starts on July 1</p>
                </div>

                {{-- End Date --}}
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                        End Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('end_date') border-red-400 @enderror">
                    @error('end_date') 
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">KSG FY ends on June 30</p>
                </div>

                {{-- Is Current --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="is_current" id="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}
                        class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <label for="is_current" class="text-sm font-medium text-gray-700">Set as current financial year</label>
                        <p class="text-xs text-gray-500">Mark this as the active financial year</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg">
                        Create Financial Year
                    </button>
                    <a href="{{ route('admin.financial-years.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>

        {{-- Helper Card --}}
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">After Creating:</h3>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>Planning weeks will be auto-generated when accessed</li>
                <li>Week references will follow format: KSG/NRB/YY-YY/Week-XXX</li>
                <li>You can generate weeks for other campuses (MTB, BRC, LWR) later</li>
            </ul>
        </div>
    </div>
</x-layouts.admin>
