<x-layouts.admin>
    <x-slot:title>New Event</x-slot:title>

    <div class="max-w-5xl" x-data="eventCreator()" x-init="init()">

        <form method="POST" action="{{ route('admin.events.store') }}" class="space-y-6" id="event-form">
            @csrf

            {{-- ── Step 1: Event details ─────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                    Event Details
                </h2>
                <div class="space-y-5">
                    {{-- Planning Week --}}
                    <div>
                        <label for="planning_week_id" class="block text-sm font-medium text-gray-700 mb-1">Planning Week <span class="text-red-500">*</span></label>
                        <select name="planning_week_id" id="planning_week_id"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('planning_week_id') border-red-400 @enderror">
                            <option value="">Select a week…</option>
                            @foreach ($weeks as $week)
                                <option value="{{ $week->id }}" @selected(old('planning_week_id') == $week->id)>
                                    {{ $week->label }}{{ $week->is_current ? ' (Current)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('planning_week_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Event Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Event Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            placeholder="e.g. Annual General Meeting 2026"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Event Type --}}
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Event Type <span class="text-red-500">*</span></label>
                        <select name="type" id="type" x-model="selectedType" @change="loadTemplates()"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @error('type') border-red-400 @enderror">
                            <option value="">Select event type…</option>
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                            @endforeach
                            <option value="__custom__">+ Other (type below)</option>
                        </select>
                        <input type="text" id="type_custom" x-show="selectedType === '__custom__'"
                            @input="customType = $event.target.value"
                            placeholder="Enter event type…"
                            class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Date & Time --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="event_date" class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}"
                                x-model="eventDate"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="event_time" class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                            <input type="time" name="event_time" id="event_time" value="{{ old('event_time') }}"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Venue --}}
                    <div>
                        <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                        <input type="text" name="venue" id="venue" value="{{ old('venue') }}"
                            placeholder="e.g. Main Boardroom"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="description" rows="2"
                            placeholder="Optional notes…"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── Step 2: Predefined requirements checklist ─────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6"
                 x-show="selectedType && selectedType !== '__custom__' && hasTemplates">

                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                        Predefined Requirements
                        <span class="text-xs font-normal text-gray-500">— tick to include, then set priority &amp; deadline</span>
                    </h2>
                    <div class="flex gap-3">
                        <button type="button" @click="selectAll()" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Select all</button>
                        <button type="button" @click="deselectAll()" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Clear</button>
                    </div>
                </div>

                {{-- Column headers --}}
                <div class="hidden sm:grid grid-cols-[1fr_120px_130px] gap-2 px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100 mt-4">
                    <span>Requirement</span>
                    <span>Priority</span>
                    <span>Deadline</span>
                </div>

                <div class="space-y-3 mt-2">
                    <template x-for="(deptTemplates, deptId) in currentTemplates" :key="deptId">
                        <div class="border border-gray-100 rounded-lg overflow-hidden">
                            {{-- Dept header --}}
                            <div class="px-4 py-2.5 bg-gray-50 flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full shrink-0"
                                     :style="'background-color:' + (deptColors[deptId] || '#6366f1')"></div>
                                <span class="text-sm font-semibold text-gray-800" x-text="deptNames[deptId]"></span>
                                <span class="text-xs text-gray-400 ml-1"
                                      x-text="'(' + deptTemplates.filter(t => selectedIds.includes(t.id)).length + '/' + deptTemplates.length + ' selected)'"></span>
                            </div>

                            {{-- Requirement rows --}}
                            <ul class="divide-y divide-gray-50">
                                <template x-for="tmpl in deptTemplates" :key="tmpl.id">
                                    <li class="px-4 py-2.5 hover:bg-gray-50/60 transition-colors"
                                        :class="selectedIds.includes(tmpl.id) ? 'bg-indigo-50/30' : ''">

                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                            {{-- Checkbox + description --}}
                                            <div class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer"
                                                 @click="toggleTemplate(tmpl.id)">
                                                <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors"
                                                     :class="selectedIds.includes(tmpl.id)
                                                        ? 'bg-indigo-600 border-indigo-600 text-white'
                                                        : 'border-gray-300 bg-white'">
                                                    <svg x-show="selectedIds.includes(tmpl.id)" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                                <span class="text-sm text-gray-700 leading-snug" x-text="tmpl.description"></span>
                                                {{-- Hidden checkbox for form submission --}}
                                                <input type="checkbox" :name="'template_ids[]'" :value="tmpl.id"
                                                       :checked="selectedIds.includes(tmpl.id)" class="sr-only">
                                            </div>

                                            {{-- Priority selector (only active when selected) --}}
                                            <div class="flex items-center gap-2 shrink-0" x-show="selectedIds.includes(tmpl.id)">
                                                <select :name="'template_priority[' + tmpl.id + ']'"
                                                    x-model="templatePriorities[tmpl.id]"
                                                    class="rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-28"
                                                    @click.stop>
                                                    <option value="critical" :selected="tmpl.priority === 'critical'">🔴 Critical</option>
                                                    <option value="high"     :selected="tmpl.priority === 'high'">🟠 High</option>
                                                    <option value="medium"   :selected="tmpl.priority === 'medium'">🟡 Medium</option>
                                                    <option value="low"      :selected="tmpl.priority === 'low'">⚪ Low</option>
                                                </select>

                                                {{-- Deadline (auto-computed from deadline_days_before, editable) --}}
                                                <input type="date"
                                                    :name="'template_deadline[' + tmpl.id + ']'"
                                                    :value="computedDeadline(tmpl)"
                                                    class="rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-32"
                                                    @click.stop>
                                            </div>

                                            {{-- Priority hint when not selected --}}
                                            <div x-show="!selectedIds.includes(tmpl.id)" class="shrink-0">
                                                <span class="text-xs px-2 py-0.5 rounded font-medium"
                                                      :class="{
                                                          'bg-red-100 text-red-700':    tmpl.priority === 'critical',
                                                          'bg-orange-100 text-orange-700': tmpl.priority === 'high',
                                                          'bg-amber-100 text-amber-700':  tmpl.priority === 'medium',
                                                          'bg-gray-100 text-gray-500':    tmpl.priority === 'low',
                                                      }"
                                                      x-text="tmpl.priority.charAt(0).toUpperCase() + tmpl.priority.slice(1)">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>

            {{-- No templates yet --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800"
                 x-show="selectedType && selectedType !== '__custom__' && !hasTemplates">
                <p class="font-medium">No predefined requirements for this event type yet.</p>
                <p class="text-xs mt-1">Add custom requirements below — they'll be saved to the template library for future events of this type.</p>
            </div>

            {{-- ── Step 3: Custom requirements ───────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">3</span>
                    Add Custom Requirements
                    <span class="text-xs font-normal text-gray-500">— saved to the template library automatically</span>
                </h2>

                {{-- Column headers --}}
                <div class="hidden sm:grid grid-cols-[1fr_140px_110px_130px_110px_24px] gap-2 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                    <span>Description</span>
                    <span>Department</span>
                    <span>Priority</span>
                    <span>Deadline</span>
                    <span>Officer</span>
                    <span></span>
                </div>

                <div class="space-y-2">
                    <template x-for="(row, index) in customRows" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-[1fr_140px_110px_130px_110px_24px] gap-2 items-start">
                            <input type="text" :name="'custom_reqs[' + index + '][description]'"
                                placeholder="Requirement description…"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">

                            <select :name="'custom_reqs[' + index + '][department_id]'"
                                class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Department…</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>

                            <select :name="'custom_reqs[' + index + '][priority]'"
                                class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="critical">🔴 Critical</option>
                                <option value="high">🟠 High</option>
                                <option value="medium" selected>🟡 Medium</option>
                                <option value="low">⚪ Low</option>
                            </select>

                            <input type="date" :name="'custom_reqs[' + index + '][deadline]'"
                                class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">

                            <input type="text" :name="'custom_reqs[' + index + '][officer]'"
                                placeholder="Officer"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">

                            <button type="button" @click="removeCustomRow(index)"
                                class="text-gray-300 hover:text-red-400 transition-colors mt-1.5 shrink-0 justify-self-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="addCustomRow()"
                    class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add row
                </button>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-6 py-2.5 rounded-lg transition-colors">
                    Create Event
                </button>
                <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const allTemplates = @json($allTemplates);
        const deptNames    = @json($departments->pluck('name', 'id'));
        const deptColors   = @json($departments->pluck('color', 'id'));

        function eventCreator() {
            return {
                selectedType: '{{ old('type') }}',
                customType: '',
                eventDate: '{{ old('event_date') }}',
                currentTemplates: {},
                hasTemplates: false,
                selectedIds: [],
                templatePriorities: {},   // tmpl.id → priority value
                customRows: [],

                init() {
                    if (this.selectedType && this.selectedType !== '__custom__') {
                        this.loadTemplates();
                    }
                },

                loadTemplates() {
                    const type = this.selectedType;
                    if (!type || type === '__custom__') {
                        this.currentTemplates = {};
                        this.hasTemplates = false;
                        this.selectedIds = [];
                        this.templatePriorities = {};
                        return;
                    }
                    const raw = allTemplates[type] || {};
                    this.currentTemplates = raw;
                    this.hasTemplates = Object.keys(raw).length > 0;

                    // Auto-select all and pre-fill priorities from template defaults
                    this.selectedIds = [];
                    this.templatePriorities = {};
                    Object.values(raw).forEach(items => {
                        items.forEach(t => {
                            this.selectedIds.push(t.id);
                            this.templatePriorities[t.id] = t.priority;
                        });
                    });
                },

                toggleTemplate(id) {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx === -1) {
                        this.selectedIds.push(id);
                    } else {
                        this.selectedIds.splice(idx, 1);
                    }
                },

                selectAll() {
                    this.selectedIds = [];
                    Object.values(this.currentTemplates).forEach(items => {
                        items.forEach(t => this.selectedIds.push(t.id));
                    });
                },

                deselectAll() {
                    this.selectedIds = [];
                },

                /**
                 * Compute the deadline date from deadline_days_before and the event date.
                 * Returns a yyyy-mm-dd string or '' if not computable.
                 */
                computedDeadline(tmpl) {
                    if (!tmpl.deadline_days_before || !this.eventDate) return '';
                    const d = new Date(this.eventDate);
                    if (isNaN(d)) return '';
                    d.setDate(d.getDate() - tmpl.deadline_days_before);
                    return d.toISOString().split('T')[0];
                },

                addCustomRow() {
                    this.customRows.push({});
                },

                removeCustomRow(index) {
                    this.customRows.splice(index, 1);
                },
            };
        }
    </script>
    @endpush
</x-layouts.admin>
