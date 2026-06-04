@props([
    'href' => null,
    'icon' => null,
    'shortcut' => null,
    'active'   => false,
    'danger'   => false,
])

@php
    $cls = \Illuminate\Support\Arr::toCssClasses([
        'dropdown-item',
        'dropdown-item-danger' => $danger,
    ]);
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $cls }}" data-active="{{ $active ? 'true' : 'false' }}" role="menuitem">
        @if($icon) <x-shared.icon :name="$icon" size="13"/> @endif
        <span style="flex:1;">{{ $slot }}</span>
        @if($shortcut) <x-badges.kbd>{{ $shortcut }}</x-badges.kbd> @endif
    </a>
@else
    <button type="button" class="{{ $cls }}" data-active="{{ $active ? 'true' : 'false' }}" role="menuitem" {{ $attributes }}>
        @if($icon) <x-shared.icon :name="$icon" size="13"/> @endif
        <span style="flex:1;">{{ $slot }}</span>
        @if($shortcut) <x-badges.kbd>{{ $shortcut }}</x-badges.kbd> @endif
    </button>
@endif
