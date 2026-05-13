@props(['percentage'])

@php
    $classes = \App\Models\Department::ragClasses((int) $percentage);
    $status  = \App\Models\Department::ragStatus((int) $percentage);
@endphp

<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes['bg'] }} {{ $classes['text'] }}">
    <span class="w-1.5 h-1.5 rounded-full
        @if((int)$percentage === 100) bg-green-500
        @elseif((int)$percentage > 0) bg-amber-400
        @else bg-red-400
        @endif
    "></span>
    {{ $status }}
</span>
