@props([
    'name' => null,
    'options' => [],        // [['value' => 'a', 'label' => 'A'], …]
    'value' => null,
    'placeholder' => null,
    'size' => 'md',
])

@php
    $value = $value ?? ($name ? old($name) : null);
    $invalid = $name && isset($errors) && $errors->has($name);
    $selectClass = \Illuminate\Support\Arr::toCssClasses([
        'select',
        'select-sm' => $size === 'sm',
        'select-lg' => $size === 'lg',
    ]);
@endphp

<select
    @if($name) name="{{ $name }}" id="{{ $name }}" @endif
    aria-invalid="{{ $invalid ? 'true' : 'false' }}"
    {{ $attributes->merge(['class' => $selectClass]) }}>
    @if($placeholder) <option value="">{{ $placeholder }}</option> @endif
    @foreach($options as $opt)
        <option value="{{ $opt['value'] }}" @selected($value === $opt['value'])>
            {{ $opt['label'] }}
        </option>
    @endforeach
</select>
