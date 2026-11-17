<x-layouts.public title="Book Event - Step {{ $step }} of 6">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Progress Indicator --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Step {{ $step }} of 6</span>
                <span class="text-sm text-gray-500">{{ round(($step / 6) * 100) }}% Complete</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                     style="width: {{ ($step / 6) * 100 }}%"></div>
            </div>
        </div>

        {{-- Step Titles --}}
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                @if($step === 1)
                    Organization & Contact Information
                @elseif($step === 2)
                    Event Information
                @elseif($step === 3)
                    Event Dates & Scheduling
                @elseif($step === 4)
                    Venue & Accommodation
                @elseif($step === 5)
                    Equipment & Technical Requirements
                @else
                    Catering & Hospitality
                @endif
            </h1>
            <p class="text-gray-600 mb-6">
                @if($step === 1)
                    Please provide details about your organization and the main contact person.
                @elseif($step === 2)
                    Tell us about the event you're planning to organize.
                @elseif($step === 3)
                    Select your preferred dates and timing for the event.
                @elseif($step === 4)
                    Choose your venue and indicate accommodation needs.
                @elseif($step === 5)
                    Select the equipment and technical support you'll need.
                @else
                    Specify your catering and hospitality requirements.
                @endif
            </p>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                    <p class="font-semibold mb-2">Please correct the following errors:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bookings.step.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="step" value="{{ $step }}">

                @if($step === 1)
                    @include('bookings.steps.step1')
                @elseif($step === 2)
                    @include('bookings.steps.step2')
                @elseif($step === 3)
                    @include('bookings.steps.step3')
                @elseif($step === 4)
                    @include('bookings.steps.step4')
                @elseif($step === 5)
                    @include('bookings.steps.step5')
                @else
                    @include('bookings.steps.step6')
                @endif

                {{-- Navigation Buttons --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    @if($step > 1)
                        <a href="{{ route('bookings.step', ['step' => $step - 1]) }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-700">
                            ← Previous
                        </a>
                    @else
                        <a href="{{ route('bookings.landing') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-700">
                            Cancel
                        </a>
                    @endif

                    <button type="submit" 
                            class="px-8 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold">
                        @if($step === 6)
                            Submit Booking
                        @else
                            Continue →
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.public>
