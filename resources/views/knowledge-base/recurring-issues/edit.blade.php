<x-layouts.app>
    <x-slot:title>Edit — {{ $recurringIssue->title }}</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('knowledge-base.index') }}" class="hover:text-indigo-600">Knowledge Base</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('recurring-issues.index') }}" class="hover:text-indigo-600">Recurring Issues</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('recurring-issues.show', $recurringIssue) }}" class="hover:text-indigo-600 truncate">{{ Str::limit($recurringIssue->title, 40) }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium">Edit</span>
    </nav>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-6">Edit Recurring Issue</h1>

            <form method="POST" action="{{ route('recurring-issues.update', $recurringIssue) }}" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Issue Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $recurringIssue->title) }}" required
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('title') border-red-400 @enderror">
                    @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category" required
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @foreach ($categories as $val => $label)
                                <option value="{{ $val }}" @selected(old('category', $recurringIssue->category) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Severity <span class="text-red-500">*</span></label>
                        <select name="severity" required
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @foreach (['critical' => '🔴 Critical', 'high' => '🟠 High', 'medium' => '🟡 Medium', 'low' => '⚪ Low'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('severity', $recurringIssue->severity) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Affected Department</label>
                    <select name="department_id"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Organisation-wide</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $recurringIssue->department_id) == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('description', $recurringIssue->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Root Cause</label>
                    <textarea name="root_cause" rows="2"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('root_cause', $recurringIssue->root_cause) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Recommended Fix</label>
                    <textarea name="recommended_fix" rows="2"
                        class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('recommended_fix', $recurringIssue->recommended_fix) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-xl transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('recurring-issues.show', $recurringIssue) }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
