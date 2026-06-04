@props([
    'name' => null,
    'value' => '1',
    'checked' => false,
    'label' => null,
    'hint' => null,
])

<label class="field-row" style="cursor:pointer; justify-content: space-between;">
    @if($label)
        <span style="display:flex;flex-direction:column;gap:2px;">
            <span class="label" style="margin: 0;">{{ $label }}</span>
            @if($hint)<span class="hint">{{ $hint }}</span>@endif
        </span>
    @endif
    <input type="checkbox" class="switch" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes }}/>
</label>
