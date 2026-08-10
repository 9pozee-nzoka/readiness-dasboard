<x-layouts.admin>
    <x-slot:title>New Planning Week</x-slot:title>

    <div class="max-w-xl">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-5">Create planning week</h2>
            <form method="POST" action="{{ route('admin.planning-weeks.store') }}" class="space-y-5">
                @csrf
                @include('admin.planning-weeks._form')
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg">Create week</button>
                    <a href="{{ route('admin.planning-weeks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
