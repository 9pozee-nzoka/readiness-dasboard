@props(['percentage', 'showLabel' => true])

@php
    $classes = \App\Models\Department::ragClasses((int) $percentage);
@endphp

<div class="flex items-center gap-3">
    <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
        <div class="{{ $classes['bar'] }} h-2 rounded-full transition-all duration-500"
             style="width: {{ $percentage }}%"></div>
    </div>
    @if($showLabel)
        <span class="text-sm font-semibold {{ $classes['text'] }} w-10 text-right">{{ $percentage }}%</span>
    @endif
</div>
