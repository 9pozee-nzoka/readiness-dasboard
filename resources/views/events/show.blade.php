<x-layouts.app>
    <x-slot:title>{{ $event->name }} — Readiness</x-slot:title>

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('dashboard.index') }}" class="hover:text-indigo-600">Dashboard</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 font-medium truncate">{{ $event->name }}</span>
    </nav>

    @php
        $overall  = $event->overallReadiness();
        $weighted = $event->weightedReadiness();
        $classes  = \App\Models\Department::ragClasses($overall);
        $user     = auth()->user();
        $criticalPending = $event->criticalPendingCount();
    @endphp

    {{-- Event header --}}
    <div class="bg-white rounded-xl border {{ $event->isAtRisk() ? 'border-red-300' : 'border-gray-200' }} shadow-sm p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ $event->name }}</h1>
                    <x-rag-badge :percentage="$overall" />
                    @if ($event->isAtRisk())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                            ⚠ At Risk
                        </span>
                    @endif
                    @if ($criticalPending > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
                            {{ $criticalPending }} critical pending
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                    <span>{{ $event->event_date->format('l, d F Y') }}</span>
                    @if ($event->event_time)
                        <span>&bull; {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }}</span>
                    @endif
                    @if ($event->venue) <span>&bull; {{ $event->venue }}</span> @endif
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs font-medium">{{ $event->type }}</span>
                </div>
                @if ($event->description)
                    <p class="mt-2 text-sm text-gray-600">{{ $event->description }}</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0 print:hidden">
                <a href="{{ route('events.export', $event) }}"
                    class="inline-flex items-center gap-1.5 border border-gray-300 hover:border-gray-400 bg-white text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </a>
                <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 border border-gray-300 hover:border-gray-400 bg-white text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
                @if ($user->canManageEvents())
                    <a href="{{ route('events.edit', $event) }}"
                        class="inline-flex items-center gap-1.5 border border-gray-300 hover:border-gray-400 bg-white text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                @endif
            </div>
        </div>

        {{-- Dual progress bars --}}
        <div class="mt-5 space-y-2">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-500">Overall Readiness</span>
                    <span id="overall-pct" class="text-sm font-bold {{ $classes['text'] }}">{{ $overall }}%</span>
                </div>
                <div class="bg-gray-200 rounded-full h-2.5 overflow-hidden">
                    <div id="overall-bar" class="{{ $classes['bar'] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $overall }}%"></div>
                </div>
            </div>
            <div>
                @php $wc = \App\Models\Department::ragClasses($weighted); @endphp
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-500">Weighted Readiness <span class="text-gray-400 font-normal">(critical tasks count more)</span></span>
                    <span id="weighted-pct" class="text-sm font-bold {{ $wc['text'] }}">{{ $weighted }}%</span>
                </div>
                <div class="bg-gray-200 rounded-full h-1.5 overflow-hidden">
                    <div id="weighted-bar" class="{{ $wc['bar'] }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $weighted }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Priority filter --}}
    <div class="flex flex-wrap items-center gap-2 mb-5 print:hidden">
        <span class="text-xs font-medium text-gray-500">Filter:</span>
        @foreach (['' => 'All', 'critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low', 'pending' => 'Pending only'] as $val => $label)
            <a href="{{ route('events.show', array_merge(['event' => $event->id], $val ? ['priority' => $val] : [])) }}"
                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors
                    {{ $priorityFilter === $val
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Department cards --}}
    <div class="space-y-4">
        @foreach ($departmentReadiness as $dr)
            @php $dc = $dr['classes']; @endphp

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" id="dept-{{ $dr['department']->id }}">

                {{-- Dept header --}}
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $dr['department']->color }}"></div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-semibold text-gray-900">{{ $dr['department']->name }}</h2>
                                @if ($dr['criticalPending'] > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                        {{ $dr['criticalPending'] }} critical
                                    </span>
                                @endif
                            </div>
                            @if ($dr['department']->head_name)
                                <p class="text-xs text-gray-500">{{ $dr['department']->head_name }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-xs text-gray-500">
                            <span class="dept-completed-{{ $dr['department']->id }}">{{ $dr['completed'] }}</span>/{{ $dr['total'] }} done
                        </span>
                        <span id="badge-{{ $dr['department']->id }}">
                            <x-rag-badge :percentage="$dr['percentage']" />
                        </span>
                        <span class="text-xs text-gray-400">Weighted: <span id="weighted-dept-{{ $dr['department']->id }}" class="font-semibold text-gray-600">{{ $dr['weighted'] }}%</span></span>

                        @if ($user->canSendReminders() && ($user->isAdmin() || $user->department_id === $dr['department']->id))
                            @if ($dr['percentage'] < 100)
                                <form method="POST" action="{{ route('reminders.send', $event) }}" class="print:hidden">
                                    @csrf
                                    <input type="hidden" name="department_id" value="{{ $dr['department']->id }}">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2.5 py-1 rounded-lg transition-colors"
                                        onclick="return confirm('Send reminder to {{ $dr['department']->name }} staff?')">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        Remind
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Dept progress bar --}}
                <div class="px-6 pb-3">
                    <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="dept-bar-{{ $dr['department']->id }} {{ $dc['bar'] }} h-2 rounded-full transition-all duration-500"
                             style="width: {{ $dr['percentage'] }}%"></div>
                    </div>
                </div>

                {{-- Requirements --}}
                <details class="group" {{ $user->isDepartmentScoped() ? 'open' : '' }}>
                    <summary class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs font-medium text-gray-500 cursor-pointer hover:bg-gray-100 transition-colors list-none flex items-center justify-between print:hidden">
                        <span>Requirements ({{ $dr['total'] }})</span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>

                    <div class="border-t border-gray-100">
                        <ul class="divide-y divide-gray-50">
                            @foreach ($dr['requirements'] as $req)
                                @php
                                    $isOverdue = $req->isOverdue();
                                    $pc = $req->priority->classes();
                                @endphp
                                <li class="px-6 py-3 flex items-start gap-3 {{ $isOverdue ? 'bg-red-50/40' : '' }}" id="req-{{ $req->id }}">

                                    {{-- Checkbox --}}
                                    @if ($user->isAdmin() || ($user->isDepartmentScoped() && $user->department_id === $dr['department']->id))
                                        <button type="button"
                                            onclick="toggleRequirement({{ $req->id }}, {{ $dr['department']->id }})"
                                            class="mt-0.5 shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                                                {{ $req->is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-indigo-400 bg-white' }}"
                                            aria-label="{{ $req->is_completed ? 'Mark as pending' : 'Mark as completed' }}"
                                            id="check-{{ $req->id }}">
                                            @if ($req->is_completed)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                        </button>
                                    @else
                                        <div class="mt-0.5 shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center
                                            {{ $req->is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-200 bg-gray-50' }}">
                                            @if ($req->is_completed)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start gap-2 flex-wrap">
                                            <p class="text-sm text-gray-800 req-desc-{{ $req->id }} {{ $req->is_completed ? 'line-through text-gray-400' : '' }}">
                                                {{ $req->description }}
                                            </p>
                                            {{-- Priority badge --}}
                                            <x-priority-badge :priority="$req->priority" />
                                            {{-- Escalated badge --}}
                                            @if ($req->is_escalated)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                                    ↑ Escalated
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5">
                                            @if ($req->responsible_officer)
                                                <span class="text-xs text-gray-400">{{ $req->responsible_officer }}</span>
                                            @endif
                                            @if ($req->deadline)
                                                <span class="text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                                    Due {{ $req->deadline->format('d M Y') }}{{ $isOverdue ? ' — OVERDUE' : '' }}
                                                </span>
                                            @endif
                                            @if ($req->completed_at)
                                                <span class="text-xs text-green-500 req-time-{{ $req->id }}">
                                                    ✓ {{ $req->completed_at->format('d M Y H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Admin actions --}}
                                    @if ($user->canManageEvents())
                                        <div class="flex items-center gap-1.5 shrink-0 print:hidden">
                                            {{-- Escalate --}}
                                            @if (!$req->is_completed && !$req->is_escalated && in_array($req->priority->value, ['critical', 'high']))
                                                <form method="POST" action="{{ route('requirements.escalate', $req) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                        class="text-xs text-purple-600 hover:text-purple-800 font-medium"
                                                        title="Escalate this requirement">↑</button>
                                                </form>
                                            @endif
                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('requirements.destroy', $req) }}"
                                                onsubmit="return confirm('Remove this requirement?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-gray-300 hover:text-red-400 transition-colors" aria-label="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        {{-- Add requirement --}}
                        @if ($user->canManageEvents())
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 print:hidden">
                                <form method="POST" action="{{ route('requirements.store') }}" class="flex flex-wrap gap-2">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                    <input type="hidden" name="department_id" value="{{ $dr['department']->id }}">
                                    <input type="text" name="description" placeholder="Add a requirement…"
                                        class="flex-1 min-w-40 text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        required>
                                    <select name="priority"
                                        class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        @foreach (\App\Enums\Priority::cases() as $p)
                                            <option value="{{ $p->value }}" @selected($p->value === 'medium')>{{ $p->label() }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="deadline"
                                        class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    <input type="text" name="responsible_officer" placeholder="Officer"
                                        class="w-36 text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    <button type="submit"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                                        Add
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </details>
            </div>
        @endforeach
    </div>

    @if ($user->canManageEvents())
        <div class="mt-8 pt-6 border-t border-gray-200 print:hidden">
            <form method="POST" action="{{ route('events.destroy', $event) }}"
                onsubmit="return confirm('Delete this event and all its requirements?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium transition-colors">
                    Delete this event
                </button>
            </form>
        </div>
    @endif

    @push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        async function toggleRequirement(reqId, deptId) {
            const btn = document.getElementById(`check-${reqId}`);
            if (!btn) return;
            btn.disabled = true;

            try {
                const res = await fetch(`/requirements/${reqId}/toggle`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error('Request failed');
                const data = await res.json();

                // Update checkbox
                if (data.is_completed) {
                    btn.classList.add('bg-green-500', 'border-green-500', 'text-white');
                    btn.classList.remove('border-gray-300', 'hover:border-indigo-400', 'bg-white');
                    btn.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
                    btn.setAttribute('aria-label', 'Mark as pending');
                } else {
                    btn.classList.remove('bg-green-500', 'border-green-500', 'text-white');
                    btn.classList.add('border-gray-300', 'hover:border-indigo-400', 'bg-white');
                    btn.innerHTML = '';
                    btn.setAttribute('aria-label', 'Mark as completed');
                }

                // Strikethrough
                const desc = document.querySelector(`.req-desc-${reqId}`);
                if (desc) {
                    desc.classList.toggle('line-through', data.is_completed);
                    desc.classList.toggle('text-gray-400', data.is_completed);
                }

                // Timestamp
                const timeEl = document.querySelector(`.req-time-${reqId}`);
                if (data.is_completed && data.completed_at) {
                    if (timeEl) { timeEl.textContent = `✓ ${data.completed_at}`; }
                    else if (desc?.parentElement) {
                        const span = document.createElement('span');
                        span.className = `text-xs text-green-500 req-time-${reqId}`;
                        span.textContent = `✓ ${data.completed_at}`;
                        desc.parentElement.appendChild(span);
                    }
                } else if (timeEl) { timeEl.remove(); }

                // Update bars
                updateDeptBar(deptId, data.department_percentage, data.department_status, data.department_weighted);
                updateOverallBar(data.overall_percentage, data.overall_status, data.overall_weighted);

            } catch (e) { console.error(e); }
            finally { btn.disabled = false; }
        }

        function updateDeptBar(deptId, pct, status, weighted) {
            const bar = document.querySelector(`.dept-bar-${deptId}`);
            if (bar) {
                bar.style.width = `${pct}%`;
                bar.className = bar.className.replace(/bg-(green|amber|red)-\d+/, ragBarClass(pct));
            }
            const badge = document.getElementById(`badge-${deptId}`);
            if (badge) badge.innerHTML = ragBadgeHtml(pct, status);
            const wEl = document.getElementById(`weighted-dept-${deptId}`);
            if (wEl) wEl.textContent = `${weighted}%`;
        }

        function updateOverallBar(pct, status, weighted) {
            const bar   = document.getElementById('overall-bar');
            const pctEl = document.getElementById('overall-pct');
            if (bar) { bar.style.width = `${pct}%`; bar.className = bar.className.replace(/bg-(green|amber|red)-\d+/, ragBarClass(pct)); }
            if (pctEl) { pctEl.textContent = `${pct}%`; pctEl.className = pctEl.className.replace(/text-(green|amber|red)-\d+/, ragTextClass(pct)); }
            const wBar = document.getElementById('weighted-bar');
            const wPct = document.getElementById('weighted-pct');
            if (wBar) { wBar.style.width = `${weighted}%`; wBar.className = wBar.className.replace(/bg-(green|amber|red)-\d+/, ragBarClass(weighted)); }
            if (wPct) { wPct.textContent = `${weighted}%`; wPct.className = wPct.className.replace(/text-(green|amber|red)-\d+/, ragTextClass(weighted)); }
        }

        function ragBarClass(pct)  { return pct === 100 ? 'bg-green-500' : pct > 0 ? 'bg-amber-400' : 'bg-red-400'; }
        function ragTextClass(pct) { return pct === 100 ? 'text-green-700' : pct > 0 ? 'text-amber-700' : 'text-red-700'; }
        function ragBadgeHtml(pct, status) {
            const bg  = pct === 100 ? 'bg-green-100 text-green-700' : pct > 0 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700';
            const dot = pct === 100 ? 'bg-green-500' : pct > 0 ? 'bg-amber-400' : 'bg-red-400';
            return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium ${bg}"><span class="w-1.5 h-1.5 rounded-full ${dot}"></span>${status}</span>`;
        }
    </script>
    @endpush
</x-layouts.app>
