<x-layouts.admin>
    <x-slot:title>Requirement Templates</x-slot:title>

    @push('scripts')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    {{-- ── Page header ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Requirement Templates</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Predefined checklists per event type. Set priority &amp; deadline on each — they become defaults when creating events.
            </p>
        </div>
        {{-- Stats pill --}}
        @php $totalActive = $templates->flatten()->where('is_active', true)->count(); @endphp
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                {{ $totalActive }} active templates
            </span>
        </div>
    </div>

    {{-- ── Add new template ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-8 overflow-hidden" x-data="{ open: false }">
        <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="font-semibold text-gray-900 text-sm">Add New Template Requirement</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-cloak class="border-t border-gray-100 px-6 py-5 bg-gray-50/40">
            <form method="POST" action="{{ route('admin.event-type-requirements.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Event Type --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Event Type <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="event_type" value="{{ $selectedType }}"
                            placeholder="e.g. Meeting, Ceremony…"
                            list="type-list"
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                            required>
                        <datalist id="type-list">
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type }}">
                            @endforeach
                        </datalist>
                    </div>

                    {{-- Department --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select name="department_id"
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                            required>
                            <option value="">Select department…</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="description" placeholder="e.g. Set up audio-visual equipment"
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                            required>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Priority</label>
                        <select name="priority"
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                            @foreach (\App\Enums\Priority::cases() as $p)
                                <option value="{{ $p->value }}" @selected($p->value === 'medium')>
                                    {{ match($p->value) { 'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', 'low' => '⚪' } }} {{ $p->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Deadline --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Deadline</label>
                        <input type="date" name="deadline"
                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        <p class="text-[10px] text-gray-400 mt-1">Leave blank — set later per template</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Template
                    </button>
                    <button type="button" @click="open = false" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Event type filter tabs ────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.event-type-requirements.index') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                {{ ! $selectedType
                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                    : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600' }}">
            All Types
        </a>
        @php
            $typeIcons = ['Meeting' => '🤝', 'Training' => '📚', 'Ceremony' => '🎓', 'Forum' => '💬', 'Review' => '📋', 'Retreat' => '🏕️', 'Workshop' => '🔧'];
        @endphp
        @foreach ($eventTypes as $type)
            <a href="{{ route('admin.event-type-requirements.index', ['type' => $type]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                    {{ $selectedType === $type
                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200'
                        : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600' }}">
                <span>{{ $typeIcons[$type] ?? '📌' }}</span>
                {{ $type }}
            </a>
        @endforeach
    </div>

    {{-- ── Template cards ────────────────────────────────────────────── --}}
    @if ($templates->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 text-center py-20">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <p class="text-gray-500 font-medium">No templates found</p>
            <p class="text-gray-400 text-sm mt-1">Add your first template requirement above</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach ($templates as $eventType => $typeTemplates)
                @php
                    $activeCount   = $typeTemplates->where('is_active', true)->count();
                    $totalCount    = $typeTemplates->count();
                    $deptGroups    = $typeTemplates->groupBy('department_id');
                    $icon          = $typeIcons[$eventType] ?? '📌';
                    $criticalCount = $typeTemplates->where('priority.value', 'critical')->count();
                @endphp

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

                    {{-- ── Event type header ── --}}
                    <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $icon }}</span>
                            <div>
                                <h3 class="font-bold text-white text-base">{{ $eventType }}</h3>
                                <p class="text-slate-400 text-xs mt-0.5">
                                    {{ $deptGroups->count() }} department{{ $deptGroups->count() !== 1 ? 's' : '' }}
                                    &nbsp;·&nbsp; {{ $totalCount }} requirement{{ $totalCount !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($criticalCount > 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-500/20 text-red-300 border border-red-500/30">
                                    🔴 {{ $criticalCount }} critical
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/10 text-slate-300 border border-white/10">
                                {{ $activeCount }}/{{ $totalCount }} active
                            </span>
                        </div>
                    </div>

                    {{-- ── Department groups ── --}}
                    @foreach ($deptGroups as $deptId => $deptTemplates)
                        @php
                            $dept      = $deptTemplates->first()->department;
                            $deptActive = $deptTemplates->where('is_active', true)->count();
                        @endphp

                        <div class="border-b border-gray-100 last:border-0">
                            {{-- Dept header --}}
                            <div class="flex items-center justify-between px-6 py-3 bg-gray-50/60">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-3 h-3 rounded-full shrink-0 ring-2 ring-white shadow-sm"
                                         style="background-color: {{ $dept->color }}"></div>
                                    <span class="text-sm font-semibold text-gray-700">{{ $dept->name }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $deptActive }}/{{ $deptTemplates->count() }} active</span>
                            </div>

                            {{-- Requirements --}}
                            <ul class="divide-y divide-gray-50/80">
                                @foreach ($deptTemplates as $tmpl)
                                    @php $pc = $tmpl->priority->classes(); @endphp
                                    <li x-data="{ editing: false }" id="tmpl-{{ $tmpl->id }}"
                                        class="transition-colors {{ ! $tmpl->is_active ? 'opacity-40' : '' }}">

                                        {{-- ── View row ── --}}
                                        <div x-show="!editing"
                                             class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center gap-3 hover:bg-indigo-50/20 transition-colors group">

                                            {{-- Left: status dot + description --}}
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <div class="w-2 h-2 rounded-full shrink-0 {{ $tmpl->is_active ? 'bg-green-400' : 'bg-gray-300' }}"></div>
                                                <span class="text-sm text-gray-800 {{ ! $tmpl->is_active ? 'line-through' : '' }} truncate">
                                                    {{ $tmpl->description }}
                                                </span>
                                            </div>

                                            {{-- Right: badges + actions --}}
                                            <div class="flex items-center gap-2 flex-wrap shrink-0 pl-5 sm:pl-0">
                                                {{-- Priority badge --}}
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $pc['bg'] }} {{ $pc['text'] }} {{ $pc['border'] }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $pc['dot'] }}"></span>
                                                    {{ $tmpl->priority->label() }}
                                                </span>

                                                {{-- Deadline --}}
                                                @if ($tmpl->deadline)
                                                    @php $isOverdue = $tmpl->deadline->isPast(); @endphp
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium border
                                                        {{ $isOverdue ? 'bg-red-50 text-red-700 border-red-200' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        {{ $tmpl->deadline->format('d M Y') }}
                                                        @if ($isOverdue) <span class="font-bold">!</span> @endif
                                                    </span>
                                                @else
                                                    <span class="text-[11px] text-gray-300 italic">No deadline</span>
                                                @endif

                                                {{-- Divider --}}
                                                <span class="w-px h-4 bg-gray-200 hidden sm:block"></span>

                                                {{-- Action buttons --}}
                                                <div class="flex items-center gap-1">
                                                    <button type="button" @click="editing = true"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-200 transition-all">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Edit
                                                    </button>

                                                    <form method="POST" action="{{ route('admin.event-type-requirements.toggle', $tmpl) }}">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border border-transparent transition-all
                                                                {{ $tmpl->is_active
                                                                    ? 'text-amber-600 hover:bg-amber-50 hover:border-amber-200'
                                                                    : 'text-green-600 hover:bg-green-50 hover:border-green-200' }}">
                                                            {{ $tmpl->is_active ? 'Disable' : 'Enable' }}
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('admin.event-type-requirements.destroy', $tmpl) }}"
                                                        onsubmit="return confirm('Delete this template requirement?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-red-500 hover:bg-red-50 border border-transparent hover:border-red-200 transition-all">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ── Edit row ── --}}
                                        <div x-show="editing" x-cloak
                                             class="px-6 py-4 bg-indigo-50/50 border-t border-indigo-100">
                                            <form method="POST"
                                                  action="{{ route('admin.event-type-requirements.update', $tmpl) }}"
                                                  class="space-y-3">
                                                @csrf @method('PUT')

                                                <div class="grid grid-cols-1 sm:grid-cols-[1fr_160px_160px] gap-3">
                                                    {{-- Description --}}
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Description</label>
                                                        <input type="text" name="description"
                                                            value="{{ $tmpl->description }}"
                                                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                                            required>
                                                    </div>

                                                    {{-- Priority --}}
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Priority</label>
                                                        <select name="priority"
                                                            class="block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                                                            @foreach (\App\Enums\Priority::cases() as $p)
                                                                <option value="{{ $p->value }}" @selected($tmpl->priority->value === $p->value)>
                                                                    {{ match($p->value) { 'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', 'low' => '⚪' } }} {{ $p->label() }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    {{-- Deadline --}}
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Deadline</label>
                                                        <input type="date" name="deadline"
                                                            value="{{ $tmpl->deadline?->format('Y-m-d') }}"
                                                            class="block w-full rounded-xl border border-gray-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                                                        <p class="text-[10px] text-gray-400 mt-1">Leave blank for no deadline</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2 pt-1">
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Save changes
                                                    </button>
                                                    <button type="button" @click="editing = false"
                                                        class="text-sm text-gray-500 hover:text-gray-700 font-medium px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.admin>
