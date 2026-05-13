<x-layouts.app>
    <x-slot:title>Edit — {{ $event->name }}</x-slot:title>

    <div class="max-w-2xl">
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('dashboard.index') }}" class="hover:text-indigo-600">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('events.show', $event) }}" class="hover:text-indigo-600 truncate">{{ $event->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h1 class="text-lg font-bold text-gray-900 mb-6">Edit Event</h1>

            <form method="POST" action="{{ route('events.update', $event) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-event-form :weeks="$weeks" :event="$event" />

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('events.show', $event) }}"
                        class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
