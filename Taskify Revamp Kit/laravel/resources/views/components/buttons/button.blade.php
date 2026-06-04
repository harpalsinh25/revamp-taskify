@props([
    'variant' => 'secondary',  // primary | secondary | ghost | outline | danger | success
    'size'    => 'md',         // sm | md | lg
    'icon'    => null,         // optional icon name (left)
    'iconAfter' => null,       // optional icon name (right)
    'href'    => null,         // renders <a> when set
    'type'    => 'button',     // button | submit | reset
    'loading' => false,
    'disabled'=> false,
    'block'   => false,        // full width
])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'btn',
        'btn-'.$variant,
        'btn-'.$size => $size !== 'md',
        'w-full' => $block,
    ]);
    $attrs = [
        'class'        => $classes,
        'data-loading' => $loading ? 'true' : null,
        'aria-disabled'=> $disabled ? 'true' : null,
    ];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge($attrs) }}>
        @if($icon)      <x-shared.icon :name="$icon" :size="$size === 'lg' ? 14 : 12" /> @endif
        {{ $slot }}
        @if($iconAfter) <x-shared.icon :name="$iconAfter" :size="$size === 'lg' ? 14 : 12" /> @endif
    </a>
@else
    <button type="{{ $type }}" @if($disabled) disabled @endif {{ $attributes->merge($attrs) }}>
        @if($icon)      <x-shared.icon :name="$icon" :size="$size === 'lg' ? 14 : 12" /> @endif
        {{ $slot }}
        @if($iconAfter) <x-shared.icon :name="$iconAfter" :size="$size === 'lg' ? 14 : 12" /> @endif
    </button>
@endif
