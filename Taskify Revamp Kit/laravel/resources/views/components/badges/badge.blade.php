@props([
    'tone' => 'neutral',  // neutral | primary | ok | warn | err | info
])

@php
    $cls = ['badge'];
    if ($tone !== 'neutral') $cls[] = 'badge-' . $tone;
@endphp

<span {{ $attributes->merge(['class' => implode(' ', $cls)]) }}>
    {{ $slot }}
</span>
