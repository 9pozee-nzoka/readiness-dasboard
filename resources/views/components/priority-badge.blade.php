@props(['priority'])

@php
    $p = $priority instanceof \App\Enums\Priority ? $priority : \App\Enums\Priority::from($priority);
    $c = $p->classes();
@endphp

<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold border {{ $c['bg'] }} {{ $c['text'] }} {{ $c['border'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
    {{ $p->label() }}
</span>
