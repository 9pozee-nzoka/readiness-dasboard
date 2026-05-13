<x-layouts.admin>
    <x-slot:title>Requirement Templates</x-slot:title>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Requirement Templates</h2>
            <p class="text-sm text-gray-500">Predefined requirements per event type and department. These are offered as a checklist when creating events.</p>
        </div>
    </div>

    {{-- Filter by type --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.event-type-requirements.index') }}"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                {{ !$selectedType ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}">
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

    {{-- Add new template --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4 text-sm">Add Template Requirement</h3>
        <form method="POST" action="{{ route('admin.event-type-requirements.store') }}" class="flex flex-wrap gap-2">
            @csrf
            <input type="text" name="event_type" value="{{ $selectedType }}" placeholder="Event type (e.g. Meeting)"
                list="type-list"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-40"
                required>
            <datalist id="type-list">
                @foreach ($eventTypes as $type)
                    <option value="{{ $type }}">
                @endforeach
            </datalist>
            <select name="department_id"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-44"
                required>
                <option value="">Department…</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <input type="text" name="description" placeholder="Requirement description…"
                class="flex-1 min-w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                required>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Add
            </button>
        </form>
    </div>

    {{-- Templates grouped by event type --}}
    @if ($templates->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 text-center py-16">
            <p class="text-gray-400 text-sm">No templates found. Add one above.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($templates as $eventType => $typeTemplates)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">{{ $eventType }}</h3>
                        <span class="text-xs text-gray-500">{{ $typeTemplates->count() }} requirement(s)</span>
                    </div>

                    {{-- Group by department --}}
                    @foreach ($typeTemplates->groupBy('department_id') as $deptId => $deptTemplates)
                        @php $dept = $deptTemplates->first()->department; @endphp
                        <div class="border-b border-gray-50 last:border-0">
                            <div class="px-5 py-2 bg-gray-50/50 flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dept->color }}"></div>
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ $dept->name }}</span>
                            </div>
                            <ul class="divide-y divide-gray-50">
                                @foreach ($deptTemplates as $tmpl)
                                    <li class="px-5 py-2.5 flex items-center gap-3 {{ !$tmpl->is_active ? 'opacity-50' : '' }}">
                                        <div class="w-1.5 h-1.5 rounded-full shrink-0 {{ $tmpl->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                        <span class="flex-1 text-sm text-gray-700">{{ $tmpl->description }}</span>
                                        {{-- Priority badge --}}
                                        <x-priority-badge :priority="$tmpl->priority" />
                                        {{-- Deadline hint --}}
                                        @if ($tmpl->deadline_days_before)
                                            <span class="text-xs text-gray-400 shrink-0">{{ $tmpl->deadline_days_before }}d before</span>
                                        @endif
                                        <div class="flex items-center gap-2 shrink-0">
                                            <form method="POST" action="{{ route('admin.event-type-requirements.toggle', $tmpl) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="text-xs {{ $tmpl->is_active ? 'text-amber-600 hover:text-amber-700' : 'text-green-600 hover:text-green-700' }} font-medium">
                                                    {{ $tmpl->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.event-type-requirements.destroy', $tmpl) }}"
                                                onsubmit="return confirm('Delete this template requirement?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
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
