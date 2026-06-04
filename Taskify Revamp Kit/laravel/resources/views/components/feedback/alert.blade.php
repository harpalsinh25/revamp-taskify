@props([
    'type'  => 'info',   // info | success | warn | error
    'title' => null,
    'closable' => false,
])

@php
    $iconMap = ['info' => 'cmd', 'success' => 'check', 'warn' => 'bell', 'error' => 'close'];
@endphp

<div {{ $attributes->merge(['class' => 'alert alert-' . $type, 'role' => 'alert']) }}>
    <span class="alert-icon"><x-shared.icon :name="$iconMap[$type] ?? 'cmd'" size="14"/></span>
    <div class="alert-body">
        @if($title)<div class="alert-title">{{ $title }}</div>@endif
        <div>{{ $slot }}</div>
    </div>
    @if($closable)
        <button type="button" class="alert-close" aria-label="Dismiss" onclick="this.closest('.alert').remove()">
            <x-shared.icon name="close" size="14"/>
        </button>
    @endif
</div>
