<x-layouts.admin>
    <x-slot:title>Requirement Templates</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Requirement Templates</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Predefined requirements per event type and department.
                Set priority and deadline (days before the event) for each — these become the defaults when creating events.
            </p>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.event-type-requirements.index') }}"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                {{ ! $selectedType ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}">
            All Types
        </a>
        @foreach ($eventTypes as $type)
            <a href="{{ route('admin.event-type-requirements.index', ['type' => $type]) }}"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                    {{ $selectedType === $type ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}">
                {{ $type }}
            </a>
        @endforeach
    </div>

    {{-- Add new template ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Add Template Requirement</h3>
        <form method="POST" action="{{ route('admin.event-type-requirements.store') }}"
              class="grid grid-cols-1 sm:grid-cols-[140px_160px_1fr_110px_90px_auto] gap-2 items-end">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Event Type <span class="text-red-500">*</span></label>
                <input type="text" name="event_type" value="{{ $selectedType }}"
                    placeholder="e.g. Meeting"
                    list="type-list"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required>
                <datalist id="type-list">
                    @foreach ($eventTypes as $type)
                        <option value="{{ $type }}">
                    @endforeach
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Department <span class="text-red-500">*</span></label>
                <select name="department_id"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required>
                    <option value="">Select…</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Description <span class="text-red-500">*</span></label>
                <input type="text" name="description" placeholder="Requirement description…"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                <select name="priority"
                    class="block w-full rounded-lg border border-gray-300 px-2 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    @foreach (\App\Enums\Priority::cases() as $p)
                        <option value="{{ $p->value }}" @selected($p->value === 'medium')>
                            {{ match($p->value) { 'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', 'low' => '⚪' } }} {{ $p->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Days before</label>
                <input type="number" name="deadline_days_before" min="0" max="365"
                    placeholder="e.g. 2"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="flex items-end">
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Add
                </button>
            </div>
        </form>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 mb-4 text-xs text-gray-500">
        <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> Active
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-gray-300"></span> Disabled
        </span>
        <span class="text-gray-400">· Click <strong class="text-gray-600">Edit</strong> on any row to change its priority or deadline</span>
    </div>

    {{-- Templates grouped by event type ─────────────────────────── --}}
    @if ($templates->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 text-center py-16">
            <p class="text-gray-400 text-sm">No templates found. Add one above.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($templates as $eventType => $typeTemplates)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                    {{-- Type header --}}
                    <div class="px-5 py-3.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">{{ $eventType }}</h3>
                        <span class="text-xs text-gray-500">{{ $typeTemplates->count() }} requirement(s)</span>
                    </div>

                    {{-- Column headers --}}
                    <div class="hidden sm:grid grid-cols-[1fr_110px_90px_110px_auto] gap-3 px-5 py-2 bg-gray-50/60 border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <span>Requirement</span>
                        <span>Priority</span>
                        <span>Days before</span>
                        <span>Status</span>
                        <span>Actions</span>
                    </div>

                    {{-- Group by department --}}
                    @foreach ($typeTemplates->groupBy('department_id') as $deptId => $deptTemplates)
                        @php $dept = $deptTemplates->first()->department; @endphp

                        {{-- Dept sub-header --}}
                        <div class="px-5 py-2 bg-gray-50/40 border-b border-gray-50 flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dept->color }}"></div>
                            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ $dept->name }}</span>
                        </div>

                        <ul class="divide-y divide-gray-50">
                            @foreach ($deptTemplates as $tmpl)
                                <li class="{{ ! $tmpl->is_active ? 'opacity-50 bg-gray-50/50' : '' }}"
                                    x-data="{ editing: false }" id="tmpl-{{ $tmpl->id }}">

                                    {{-- ── View row ── --}}
                                    <div class="px-5 py-3 sm:grid sm:grid-cols-[1fr_110px_90px_110px_auto] gap-3 items-center"
                                         x-show="!editing">
                                        {{-- Description --}}
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-1.5 h-1.5 rounded-full shrink-0 {{ $tmpl->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                            <span class="text-sm text-gray-800 truncate">{{ $tmpl->description }}</span>
                                        </div>

                                        {{-- Priority --}}
                                        <div class="mt-1.5 sm:mt-0">
                                            <x-priority-badge :priority="$tmpl->priority" />
                                        </div>

                                        {{-- Deadline --}}
                                        <div class="mt-1 sm:mt-0">
                                            @if ($tmpl->deadline_days_before !== null)
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded">
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $tmpl->deadline_days_before }}d before
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">No deadline</span>
                                            @endif
                                        </div>

                                        {{-- Status --}}
                                        <div class="mt-1 sm:mt-0">
                                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full
                                                {{ $tmpl->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $tmpl->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                                {{ $tmpl->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="mt-2 sm:mt-0 flex items-center gap-3 shrink-0">
                                            <button type="button" @click="editing = true"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.event-type-requirements.toggle', $tmpl) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="text-xs font-medium {{ $tmpl->is_active ? 'text-amber-600 hover:text-amber-700' : 'text-green-600 hover:text-green-700' }}">
                                                    {{ $tmpl->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.event-type-requirements.destroy', $tmpl) }}"
                                                onsubmit="return confirm('Delete this template requirement?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- ── Edit row ── --}}
                                    <div class="px-5 py-3 bg-indigo-50/40 border-t border-indigo-100"
                                         x-show="editing" x-cloak>
                                        <form method="POST"
                                              action="{{ route('admin.event-type-requirements.update', $tmpl) }}"
                                              class="flex flex-wrap gap-2 items-end">
                                            @csrf @method('PUT')

                                            {{-- Description --}}
                                            <div class="flex-1 min-w-48">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                                <input type="text" name="description"
                                                    value="{{ $tmpl->description }}"
                                                    class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                                    required>
                                            </div>

                                            {{-- Priority --}}
                                            <div class="w-36">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                                                <select name="priority"
                                                    class="block w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                                    @foreach (\App\Enums\Priority::cases() as $p)
                                                        <option value="{{ $p->value }}" @selected($tmpl->priority->value === $p->value)>
                                                            {{ match($p->value) { 'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', 'low' => '⚪' } }} {{ $p->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Deadline days before --}}
                                            <div class="w-36">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                                    Days before event
                                                    <span class="text-gray-400 font-normal">(deadline)</span>
                                                </label>
                                                <div class="relative">
                                                    <input type="number" name="deadline_days_before"
                                                        value="{{ $tmpl->deadline_days_before }}"
                                                        min="0" max="365"
                                                        placeholder="e.g. 2"
                                                        class="block w-full rounded-lg border border-gray-300 pl-3 pr-8 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">d</span>
                                                </div>
                                                <p class="text-[10px] text-gray-400 mt-0.5">Leave blank for no deadline</p>
                                            </div>

                                            {{-- Buttons --}}
                                            <div class="flex items-end gap-2">
                                                <button type="submit"
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                                                    Save
                                                </button>
                                                <button type="button" @click="editing = false"
                                                    class="border border-gray-300 hover:border-gray-400 text-gray-600 text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    @push('scripts')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush
</x-layouts.admin>
