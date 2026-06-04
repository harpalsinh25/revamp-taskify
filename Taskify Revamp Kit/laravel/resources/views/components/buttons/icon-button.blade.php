@props([
    'icon',
    'size' => 'md',        // sm | md | lg
    'tooltip' => null,
    'href' => null,
])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'icon-btn',
        'icon-btn-sm' => $size === 'sm',
        'icon-btn-lg' => $size === 'lg',
    ]);
    $svgSize = ['sm' => 13, 'md' => 14, 'lg' => 16][$size] ?? 14;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'title' => $tooltip]) }}>
        <x-shared.icon :name="$icon" :size="$svgSize"/>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes, 'title' => $tooltip]) }}>
        <x-shared.icon :name="$icon" :size="$svgSize"/>
        {{ $slot }}
    </button>
@endif
