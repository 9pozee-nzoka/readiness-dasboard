<x-layouts.admin>
    <x-slot:title>Edit — {{ $event->name }}</x-slot:title>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-5">Edit Event</h2>
            <form method="POST" action="{{ route('admin.events.update', $event) }}" class="space-y-5">
                @csrf @method('PUT')
                <x-event-form :weeks="$weeks" :event="$event" />
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.events.show', $event) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
