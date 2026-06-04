@props([
    'name' => null,
    'value' => '1',
    'checked' => false,
    'label' => null,
])
@php
    $checked = $checked || ($name && old($name) == $value);
@endphp

@if($label)
<label class="check-label">
    <input type="checkbox" class="check" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes }}/>
    <span>{{ $label }}</span>
</label>
@else
    <input type="checkbox" class="check" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes }}/>
@endif
