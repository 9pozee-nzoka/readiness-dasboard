<x-layouts.app>
    <x-slot:title>{{ $isConsultation ? 'Ask HOD' : 'New Deliberation' }} — {{ $event->name }}</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('dashboard.index') }}" class="hover:text-indigo-600">Dashboard</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('events.show', $event) }}" class="hover:text-indigo-600 truncate">{{ $event->name }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('deliberations.index', $event) }}" class="hover:text-indigo-600">Deliberations</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium">{{ $isConsultation ? 'Ask HOD' : 'New Deliberation' }}</span>
    </nav>

    <div class="max-w-3xl">
        @if ($isConsultation)
            <div class="bg-sky-50 border border-sky-200 rounded-xl px-5 py-4 mb-5 flex items-start gap-3">
                <svg class="w-5 h-5 text-sky-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="font-semibold text-sky-800 text-sm">Consultation Request</p>
                    <p class="text-sky-700 text-xs mt-0.5">Ask your HOD for direction on a specific requirement. They will be notified and can respond through the comments.</p>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-5">
                {{ $isConsultation ? '🙋 Consultation Request' : '💬 New Deliberation' }}
            </h1>

            <form method="POST" action="{{ route('deliberations.store', $event) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $isConsultation ? 'consultation' : 'deliberation' }}">

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ $isConsultation ? 'What do you need guidance on?' : 'Title' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                        placeholder="{{ $isConsultation ? 'e.g. Unsure how to proceed with venue layout...' : 'Brief title for this deliberation' }}"
                        required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-400 @enderror">
                    @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="impact_level" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $isConsultation ? 'Urgency' : 'Impact level' }} <span class="text-red-500">*</span></label>
                        <select name="impact_level" id="impact_level" required
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="low" @selected(old('impact_level') === 'low')>Low</option>
                            <option value="medium" @selected(old('impact_level','medium') === 'medium')>Medium</option>
                            <option value="critical" @selected(old('impact_level') === 'critical')>{{ $isConsultation ? 'Urgent' : 'Critical' }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="decision_due_date" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $isConsultation ? 'Response needed by' : 'Decision due date' }}</label>
                        <input type="date" name="decision_due_date" id="decision_due_date" value="{{ old('decision_due_date') }}"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="requirement_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ $isConsultation ? 'Which requirement needs guidance?' : 'Linked requirement' }}
                        @if ($isConsultation)<span class="text-red-500">*</span>@endif
                    </label>
                    <select name="requirement_id" id="requirement_id" {{ $isConsultation ? 'required' : '' }}
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @unless ($isConsultation)
                            <option value="">None (event-level deliberation)</option>
                        @endunless
                        @foreach ($requirements as $req)
                            <option value="{{ $req->id }}" @selected(old('requirement_id', $selectedRequirementId) == $req->id)>
                                {{ $req->description }}{{ $req->department ? ' ('.$req->department->name.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $isConsultation ? 'Describe what you need help with' : 'Context / background' }}</label>
                    <textarea name="description" id="description" rows="4"
                        placeholder="{{ $isConsultation ? 'Explain what is blocking you. Be specific...' : 'Additional context for participants...' }}"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                </div>

                @unless ($isConsultation)
                    <div>
                        <label for="recommendation" class="block text-sm font-medium text-gray-700 mb-1.5">Recommendation</label>
                        <textarea name="recommendation" id="recommendation" rows="3" placeholder="What is being proposed or recommended?"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('recommendation') }}</textarea>
                    </div>

                    <div>
                        <label for="participants" class="block text-sm font-medium text-gray-700 mb-1.5">Direct to (participants)</label>
                        <select name="participants[]" id="participants" multiple size="5"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach ($participants as $participant)
                                <option value="{{ $participant->id }}" @selected(in_array($participant->id, old('participants', [])))>
                                    {{ $participant->name }} — {{ $participant->role->label() }}{{ $participant->department ? ' ('.$participant->department->name.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Hold Ctrl/Cmd to select multiple.</p>
                    </div>
                @else
                    <div class="bg-sky-50 border border-sky-100 rounded-lg px-4 py-3 text-xs text-sky-700">
                        <strong>Note:</strong> Your Head of Department will be automatically notified and can respond via comments.
                    </div>
                @endunless

                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1.5">Attachments <span class="text-gray-400 font-normal">(optional, max 10MB each)</span></label>
                    <input type="file" name="attachments[]" id="attachments" multiple
                        class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border file:border-gray-300 file:text-sm file:font-medium file:bg-white file:text-gray-700 hover:file:bg-gray-50">
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="inline-flex items-center gap-2 font-medium text-sm px-5 py-2.5 rounded-lg transition-colors {{ $isConsultation ? 'bg-sky-600 hover:bg-sky-700 text-white' : 'bg-purple-600 hover:bg-purple-700 text-white' }}">
                        {{ $isConsultation ? 'Submit Consultation Request' : 'Create Deliberation' }}
                    </button>
                    <a href="{{ route('deliberations.index', $event) }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
