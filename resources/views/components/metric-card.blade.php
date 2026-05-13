@props(['label', 'value', 'icon', 'color' => 'indigo'])

@php
    $colorMap = [
        'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'value' => 'text-indigo-700'],
        'green'  => ['bg' => 'bg-green-50',  'icon' => 'text-green-600',  'value' => 'text-green-700'],
        'amber'  => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-600',  'value' => 'text-amber-700'],
        'red'    => ['bg' => 'bg-red-50',    'icon' => 'text-red-600',    'value' => 'text-red-700'],
        'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600',   'value' => 'text-blue-700'],
    ];
    $c = $colorMap[$color] ?? $colorMap['indigo'];
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col gap-2">
    <div class="w-9 h-9 rounded-lg {{ $c['bg'] }} flex items-center justify-center">
        @if ($icon === 'calendar')
            <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        @elseif ($icon === 'check-circle')
            <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif ($icon === 'clock')
            <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif ($icon === 'x-circle')
            <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif ($icon === 'chart-bar')
            <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        @endif
    </div>
    <div>
        <p class="text-2xl font-bold {{ $c['value'] }}">{{ $value }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
    </div>
</div>
