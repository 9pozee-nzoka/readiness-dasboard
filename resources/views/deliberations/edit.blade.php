<x-layouts.app>
    <x-slot:title>Edit Deliberation</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('dashboard.index') }}" class="hover:text-indigo-600">Dashboard</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('events.show', $event) }}" class="hover:text-indigo-600 truncate">{{ $event->name }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('deliberations.show', [$event, $deliberation]) }}" class="hover:text-indigo-600 truncate">{{ $deliberation->title }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 font-medium">Edit</span>
    </nav>

    <div class="max-w-3xl bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-5">Edit Deliberation</h1>

        <form method="POST" action="{{ route('deliberations.update', [$event, $deliberation]) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $deliberation->title) }}" required
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="impact_level" class="block text-sm font-medium text-gray-700 mb-1.5">Impact level <span class="text-red-500">*</span></label>
                    <select name="impact_level" id="impact_level" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="low" @selected(old('impact_level', $deliberation->impact_level) === 'low')>Low</option>
                        <option value="medium" @selected(old('impact_level', $deliberation->impact_level) === 'medium')>Medium</option>
                        <option value="critical" @selected(old('impact_level', $deliberation->impact_level) === 'critical')>Critical</option>
                    </select>
                </div>
                <div>
                    <label for="decision_due_date" class="block text-sm font-medium text-gray-700 mb-1.5">Decision due date</label>
                    <input type="date" name="decision_due_date" id="decision_due_date" value="{{ old('decision_due_date', $deliberation->decision_due_date?->format('Y-m-d')) }}"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label for="requirement_id" class="block text-sm font-medium text-gray-700 mb-1.5">Linked requirement</label>
                <select name="requirement_id" id="requirement_id"
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">None (event-level deliberation)</option>
                    @foreach ($requirements as $req)
                        <option value="{{ $req->id }}" @selected(old('requirement_id', $deliberation->requirement_id) == $req->id)>
                            {{ $req->description }}
                            @if ($req->department) ({{ $req->department->name }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="recommendation" class="block text-sm font-medium text-gray-700 mb-1.5">Recommendation</label>
                <textarea name="recommendation" id="recommendation" rows="4" placeholder="What is being proposed or recommended?"
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('recommendation', $deliberation->recommendation) }}</textarea>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Context / background</label>
                <textarea name="description" id="description" rows="3"
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $deliberation->description) }}</textarea>
            </div>

            <div>
                <label for="participants" class="block text-sm font-medium text-gray-700 mb-1.5">Direct to (participants)</label>
                <select name="participants[]" id="participants" multiple size="5"
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach ($participants as $user)
                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('participants', $selectedParticipants)))>
                            {{ $user->name }} — {{ $user->role->label() }}
                            @if ($user->department) ({{ $user->department->name }}) @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple individuals.</p>
            </div>

            @if ($deliberation->attachments->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Existing attachments</label>
                    <div class="space-y-2">
                        @foreach ($deliberation->attachments as $attachment)
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                                <span class="text-sm text-gray-700 truncate">{{ $attachment->original_name }}</span>
                                <a href="{{ $attachment->downloadUrl() }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Download</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1.5">Add more evidence / attachments</label>
                <input type="file" name="attachments[]" id="attachments" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                    class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-500 mt-1">Max 10 MB per file. PDFs, images, Word documents accepted.</p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition-colors">
                    Save changes
                </button>
                <a href="{{ route('deliberations.show', [$event, $deliberation]) }}"
                    class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
