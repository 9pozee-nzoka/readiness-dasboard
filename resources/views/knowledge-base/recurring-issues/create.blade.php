<x-layouts.app>
    <x-slot:title>Log Recurring Issue</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('knowledge-base.index') }}" class="hover:text-indigo-600">Knowledge Base</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('recurring-issues.index') }}" class="hover:text-indigo-600">Recurring Issues</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium">Log New Issue</span>
    </nav>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-1">Log a Recurring Issue</h1>
            <p class="text-sm text-gray-500 mb-6">Document a pattern you've observed across multiple events so it can be tracked and resolved.</p>

            <form method="POST" action="{{ route('recurring-issues.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Issue Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="e.g. AV equipment always set up too late"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('title') border-red-400 @enderror">
                    @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category" required
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">Select…</option>
                            @foreach ($categories as $val => $label)
                                <option value="{{ $val }}" @selected(old('category') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Severity <span class="text-red-500">*</span></label>
                        <select name="severity" required
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @foreach (['critical' => '🔴 Critical', 'high' => '🟠 High', 'medium' => '🟡 Medium', 'low' => '⚪ Low'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('severity', 'medium') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Affected Department</label>
                    <select name="department_id"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Organisation-wide (all departments)</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                        placeholder="Describe the issue pattern in detail…"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Root Cause</label>
                    <textarea name="root_cause" rows="2"
                        placeholder="What is causing this to happen repeatedly?"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('root_cause') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Recommended Fix</label>
                    <textarea name="recommended_fix" rows="2"
                        placeholder="What should be done to prevent this from recurring?"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('recommended_fix') }}</textarea>
                </div>

                {{-- Optional first occurrence --}}
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Log First Occurrence <span class="text-gray-400 font-normal">(optional)</span></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Event where this occurred</label>
                            <select name="event_id"
                                class="block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">Select event…</option>
                                @foreach ($events as $event)
                                    <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>
                                        {{ $event->name }} ({{ $event->event_date->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Notes on this occurrence</label>
                            <input type="text" name="occurrence_notes" value="{{ old('occurrence_notes') }}"
                                placeholder="What specifically happened?"
                                class="block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-xl transition-colors">
                        Log Issue
                    </button>
                    <a href="{{ route('recurring-issues.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
