<x-layouts.app>
    <x-slot:title>{{ $event->name }} — Readiness Report</x-slot:title>

    <style>
        @media print {
            nav, .no-print, footer, .flash-messages { display: none !important; }
            body { background: white !important; }
            main { padding: 0 !important; max-width: 100% !important; }
            .print-container { border: none !important; box-shadow: none !important; }
        }
    </style>

    <div class="no-print mb-4 flex items-center gap-3">
        <button onclick="window.print()"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Report
        </button>
        <a href="{{ route('events.show', $event) }}"
            class="text-sm text-gray-600 hover:text-gray-800 font-medium">
            ← Back to Event
        </a>
    </div>

    @php
        $overall = $event->overallReadiness();
        $weighted = $event->weightedReadiness();
        $classes = \App\Models\Department::ragClasses($overall);
    @endphp

    <div class="print-container bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        {{-- Header --}}
        <div class="text-center mb-8 border-b border-gray-200 pb-6">
            <div class="flex items-center justify-center gap-3 mb-3">
                <img src="{{ asset('assets/logos/1.png') }}" alt="" class="h-10 w-auto object-contain">
                <img src="{{ asset('assets/logos/2.png') }}" alt="" class="h-10 w-auto object-contain">
                <img src="{{ asset('assets/logos/3.png') }}" alt="" class="h-10 w-auto object-contain">
            </div>
            <h1 class="text-xl font-bold text-gray-900">Event Readiness Report</h1>
            <p class="text-sm text-gray-500 mt-1">Kenya School of Government — Weekly Event Preparedness</p>
        </div>

        {{-- Event Info --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-3">{{ $event->name }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 block text-xs uppercase">Date</span>
                    <span class="font-medium text-gray-900">{{ $event->dateRangeLabel() }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs uppercase">Time</span>
                    <span class="font-medium text-gray-900">{{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('H:i') : '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs uppercase">Venue</span>
                    <span class="font-medium text-gray-900">{{ $event->venue ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs uppercase">Type</span>
                    <span class="font-medium text-gray-900">{{ $event->type }}</span>
                </div>
                @if ($event->coordinator)
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Coordinator</span>
                        <span class="font-medium text-gray-900">{{ $event->coordinator }}</span>
                    </div>
                @endif
                @if ($event->client)
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Client</span>
                        <span class="font-medium text-gray-900">{{ $event->client }}</span>
                    </div>
                @endif
                @if ($event->totalParticipants())
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Participants</span>
                        <span class="font-medium text-gray-900">{{ $event->totalParticipants() }}</span>
                    </div>
                @endif
            </div>
            @if ($event->description)
                <p class="mt-3 text-sm text-gray-600">{{ $event->description }}</p>
            @endif
        </div>

        {{-- Readiness Summary --}}
        <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Overall Readiness</span>
                    <span class="text-lg font-bold {{ $classes['text'] }}">{{ $overall }}%</span>
                </div>
                <div class="bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="{{ $classes['bar'] }} h-3 rounded-full" style="width: {{ $overall }}%"></div>
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                @php $wc = \App\Models\Department::ragClasses($weighted); @endphp
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Weighted Readiness</span>
                    <span class="text-lg font-bold {{ $wc['text'] }}">{{ $weighted }}%</span>
                </div>
                <div class="bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="{{ $wc['bar'] }} h-3 rounded-full" style="width: {{ $weighted }}%"></div>
                </div>
            </div>
        </div>

        {{-- Department Requirements --}}
        <div class="space-y-6">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-2">Department Requirements</h3>

            @foreach ($departmentReadiness as $dr)
                @php $dc = $dr['classes']; @endphp
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-5 py-3 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $dr['department']->color }}"></div>
                            <h4 class="font-semibold text-gray-900">{{ $dr['department']->name }}</h4>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-500">{{ $dr['completed'] }}/{{ $dr['total'] }} done</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dc['bg'] }} {{ $dc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dc['bar'] }}"></span>
                                {{ $dr['percentage'] }}%
                            </span>
                        </div>
                    </div>

                    @if ($dr['requirements']->isNotEmpty())
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
                                <tr>
                                    <th class="px-5 py-2 text-left w-12">Status</th>
                                    <th class="px-5 py-2 text-left">Requirement</th>
                                    <th class="px-5 py-2 text-left">Priority</th>
                                    <th class="px-5 py-2 text-left">Officer</th>
                                    <th class="px-5 py-2 text-left">Deadline</th>
                                    <th class="px-5 py-2 text-left">Completed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($dr['requirements'] as $req)
                                    <tr class="{{ $req->is_completed ? 'bg-gray-50/50' : '' }}">
                                        <td class="px-5 py-2">
                                            @if ($req->is_completed)
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500 text-white">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border-2 border-gray-300"></span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2 {{ $req->is_completed ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                            {{ $req->description }}
                                            @if ($req->is_escalated)
                                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-700">↑ Escalated</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2">
                                            <x-priority-badge :priority="$req->priority" />
                                        </td>
                                        <td class="px-5 py-2 text-gray-500">{{ $req->responsible_officer ?? '—' }}</td>
                                        <td class="px-5 py-2 {{ $req->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                            {{ $req->deadline?->format('d M Y') ?? '—' }}
                                            @if ($req->isOverdue())
                                                <span class="text-[10px]">(OVERDUE)</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2 text-gray-500">
                                            {{ $req->completed_at?->format('d M Y H:i') ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="px-5 py-3 text-sm text-gray-400">No requirements assigned.</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Signatures --}}
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-8 border-t border-gray-200 pt-8">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-6">Prepared By</p>
                <div class="border-b border-gray-400 pb-1 mb-1">
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                </div>
                <p class="text-xs text-gray-500">{{ auth()->user()->role->label() }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ now()->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-6">Reviewed By (HOD)</p>
                <div class="border-b border-gray-400 pb-1 mb-1 h-6"></div>
                <p class="text-xs text-gray-500">Name & Signature</p>
                <p class="text-xs text-gray-400 mt-1">Date: _______________</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase mb-6">Approved By (Director)</p>
                <div class="border-b border-gray-400 pb-1 mb-1 h-6"></div>
                <p class="text-xs text-gray-500">Name & Signature</p>
                <p class="text-xs text-gray-400 mt-1">Date: _______________</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-center text-xs text-gray-400 border-t border-gray-200 pt-4">
            Generated by KSG Event Readiness Dashboard — {{ now()->format('d M Y H:i') }}
        </div>
    </div>
</x-layouts.app>
