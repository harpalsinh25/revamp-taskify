@props([
    'label'    => null,
    'name'     => null,
    'hint'     => null,
    'error'    => null,
    'required' => false,
    'optional' => false,
])

@php
    $error = $error ?? ($name && isset($errors) ? $errors->first($name) : null);
    $id = $name ? $name : uniqid('field-');
@endphp

<label class="field" for="{{ $id }}">
    @if($label)
        <span class="label {{ $required ? 'label-required' : '' }}">
            {{ $label }}
            @if($optional) <span class="label-optional">Optional</span> @endif
        </span>
    @endif
    {{ $slot }}
    @if($error)
        <span class="error">{{ $error }}</span>
    @elseif($hint)
        <span class="hint">{{ $hint }}</span>
    @endif
</label>
