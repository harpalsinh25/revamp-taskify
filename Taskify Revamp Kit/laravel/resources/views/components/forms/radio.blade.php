@props([
    'name',
    'value',
    'checked' => false,
    'label' => null,
])

<label class="radio-label">
    <input type="radio" class="radio" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes }}/>
    @if($label)<span>{{ $label }}</span>@endif
</label>
