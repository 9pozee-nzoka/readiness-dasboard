<x-layouts.admin>
    <x-slot:title>Edit — {{ $week->label }}</x-slot:title>

    <div class="max-w-xl">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-5">Edit planning week</h2>
            <form method="POST" action="{{ route('admin.planning-weeks.update', $week) }}" class="space-y-5">
                @csrf @method('PUT')
                @include('admin.planning-weeks._form', ['week' => $week])
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg">Save</button>
                    <a href="{{ route('admin.planning-weeks.show', $week) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.planning-weeks.destroy', $week) }}" class="mt-6 pt-6 border-t border-gray-100"
                onsubmit="return confirm('Delete this week and all its events?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Delete planning week</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
