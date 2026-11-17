<x-layouts.admin>
    <x-slot:title>New Planning Week</x-slot:title>

    <div class="max-w-4xl">
        <!-- FY Filter -->
        <div class="mb-4">
            <form method="GET" class="flex items-center gap-3">
                <label for="fy-filter" class="text-sm font-medium text-gray-700">Financial Year:</label>
                <select
                    id="fy-filter"
                    name="fy"
                    onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    @foreach($financialYears as $fy)
                        <option value="{{ $fy->id }}" @selected($fy->id == $selectedFyId)>
                            {{ $fy->name }} @if($fy->is_current)(Current)@endif
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(session('info'))
            <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                {{ session('info') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-5">Create planning week</h2>
            <form method="POST" action="{{ route('admin.planning-weeks.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="financial_year_id" value="{{ $selectedFyId }}">
                @include('admin.planning-weeks._form')
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg">Create week</button>
                    <a href="{{ route('admin.planning-weeks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
