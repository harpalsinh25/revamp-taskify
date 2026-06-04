@props([
    'name' => null,
    'value' => null,
    'rows' => 4,
    'placeholder' => null,
    'invalid' => false,
])

@php
    $value = $value ?? ($name ? old($name) : null);
    $invalid = $invalid || ($name && isset($errors) && $errors->has($name));
@endphp

<textarea
    @if($name) name="{{ $name }}" id="{{ $name }}" @endif
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    aria-invalid="{{ $invalid ? 'true' : 'false' }}"
    {{ $attributes->merge(['class' => 'textarea']) }}>{{ $value }}</textarea>
