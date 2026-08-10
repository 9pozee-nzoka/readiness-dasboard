<x-layouts.admin>
    <x-slot:title>Import Weekly Schedule</x-slot:title>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-2">Import KSG weekly schedule</h2>
            <p class="text-sm text-gray-500 mb-6">
                Upload a JSON file matching the weekly schedule format (programme, client, dates, participants, coordinator, venue, remarks)
                or use the bundled sample for 18–22 May 2026.
            </p>

            <form method="POST" action="{{ route('admin.planning-weeks.import.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sample schedule</label>
                    @foreach ($sampleSchedules as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-700 mb-2">
                            <input type="radio" name="sample_schedule" value="{{ $key }}" class="text-indigo-600 focus:ring-indigo-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-400">or</span></div>
                </div>

                <div>
                    <label for="schedule_file" class="block text-sm font-medium text-gray-700 mb-1">Upload JSON file</label>
                    <input type="file" name="schedule_file" id="schedule_file" accept=".json,application/json"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
                    @error('schedule_file') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="replace_existing" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Replace existing events for this week if it already exists
                </label>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg">Import</button>
                    <a href="{{ route('admin.planning-weeks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>

            <div class="mt-8 p-4 bg-gray-50 rounded-lg text-xs text-gray-600 font-mono overflow-x-auto">
                <p class="font-sans font-medium text-gray-700 mb-2 text-sm">Expected JSON structure:</p>
                <pre>{
  "reference": "KSG/12/WS/01",
  "campus": "Nairobi",
  "week_start": "2026-05-18",
  "week_end": "2026-05-22",
  "items": [
    {
      "category": "new_programme|ongoing|hosted|ksg_activity|consultancy",
      "programme": "...",
      "client": "...",
      "date_start": "2026-05-18",
      "date_end": "2026-05-22",
      ...
    }
  ]
}</pre>
            </div>
        </div>
    </div>
</x-layouts.admin>
