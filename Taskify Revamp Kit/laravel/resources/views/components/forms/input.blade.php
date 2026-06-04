@props([
    'name'        => null,
    'type'        => 'text',
    'value'       => null,
    'placeholder' => null,
    'size'        => 'md',
    'icon'        => null,
    'suffix'      => null,
    'invalid'     => false,
    'disabled'    => false,
    'readonly'    => false,
])

@php
    $value = $value ?? ($name ? old($name) : null);
    $invalid = $invalid || ($name && isset($errors) && $errors->has($name));
    $inputClass = \Illuminate\Support\Arr::toCssClasses([
        'input',
        'input-sm' => $size === 'sm',
        'input-lg' => $size === 'lg',
    ]);
@endphp

@if($icon || $suffix)
<span class="input-wrap">
    @if($icon)<span class="input-icon"><x-shared.icon :name="$icon" size="13"/></span>@endif
    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" id="{{ $name }}" @endif
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        aria-invalid="{{ $invalid ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => $inputClass]) }}/>
    @if($suffix)<span class="input-suffix">{{ $suffix }}</span>@endif
</span>
@else
    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" id="{{ $name }}" @endif
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        aria-invalid="{{ $invalid ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => $inputClass]) }}/>
@endif
