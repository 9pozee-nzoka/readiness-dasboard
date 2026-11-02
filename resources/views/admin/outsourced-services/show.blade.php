<x-layouts.admin>
    <x-slot:title>Service Details</x-slot:title>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Back Button --}}
    <div class="mb-4">
        <a href="{{ route('admin.outsourced-services.index') }}" 
            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Services List
        </a>
    </div>

    {{-- Service Details Card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-5">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $outsourcedService->provider }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $outsourcedService->service_type }}</p>
                </div>
                <div class="flex gap-2">
                    @if(Auth::user()->isDirectorOrAdmin())
                        @if($outsourcedService->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.outsourced-services.approve', $outsourcedService) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    onclick="return confirm('Approve this service?')">
                                    ✓ Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.outsourced-services.reject', $outsourcedService) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                    onclick="return confirm('Reject this service?')">
                                    ✗ Reject
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.outsourced-services.edit', $outsourcedService) }}" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status Badges --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="flex flex-wrap gap-3">
                {{-- Type Badge --}}
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                    {{ $outsourcedService->type === 'hiring' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ $outsourcedService->type === 'hiring' ? '👤 Hiring' : '🔧 Outsourced Service' }}
                </span>

                {{-- Status Badge --}}
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                    {{ match($outsourcedService->status) {
                        'requested' => 'bg-gray-100 text-gray-700',
                        'approved' => 'bg-blue-100 text-blue-700',
                        'confirmed' => 'bg-green-100 text-green-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700'
                    } }}">
                    {{ ucfirst($outsourcedService->status) }}
                </span>

                {{-- Approval Status Badge --}}
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                    {{ match($outsourcedService->approval_status) {
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700'
                    } }}">
                    Approval: {{ ucfirst($outsourcedService->approval_status) }}
                </span>
            </div>
        </div>

        {{-- Main Details --}}
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left Column --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Service Information</h3>
                        
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Provider</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $outsourcedService->provider }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Service Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $outsourcedService->service_type }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Event</dt>
                                <dd class="mt-1">
                                    <a href="{{ route('events.show', $outsourcedService->event) }}" 
                                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                        {{ $outsourcedService->event->name }}
                                    </a>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Department</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $outsourcedService->department->name }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Responsible Officer</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $outsourcedService->responsibleUser->name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Duration & Cost</h3>
                        
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $outsourcedService->start_date->format('F j, Y') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $outsourcedService->end_date ? $outsourcedService->end_date->format('F j, Y') : 'Ongoing' }}
                                </dd>
                            </div>

                            @if($outsourcedService->cost)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Cost</dt>
                                <dd class="mt-1 text-lg font-bold text-gray-900">KSh {{ number_format($outsourcedService->cost, 2) }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Reason --}}
            @if($outsourcedService->reason)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Reason / Justification</h3>
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $outsourcedService->reason }}</p>
            </div>
            @endif

            {{-- Notes --}}
            @if($outsourcedService->notes)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Additional Notes</h3>
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $outsourcedService->notes }}</p>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Timeline</h3>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-xs text-gray-500">
                    <span>Created: {{ $outsourcedService->created_at->format('M j, Y \a\t H:i') }}</span>
                    @if($outsourcedService->updated_at != $outsourcedService->created_at)
                        <span class="hidden sm:inline">·</span>
                        <span>Updated: {{ $outsourcedService->updated_at->format('M j, Y \a\t H:i') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
