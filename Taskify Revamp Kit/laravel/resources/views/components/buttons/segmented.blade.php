@props([
    'options',  // array of ['value' => 'list', 'label' => 'List', 'icon' => 'list']
    'value' => null,
    'name' => null,    // hidden input name (if used in a form)
])

<div {{ $attributes->merge(['class' => 'seg']) }} role="radiogroup">
    @foreach($options as $opt)
        @php
            $isOn = ($value !== null && $value === ($opt['value'] ?? null));
            $id = uniqid('seg-');
        @endphp
        <button type="button"
                class="seg-btn {{ $isOn ? 'on' : '' }}"
                role="radio"
                aria-checked="{{ $isOn ? 'true' : 'false' }}"
                @if($name) data-name="{{ $name }}" @endif
                data-value="{{ $opt['value'] }}">
            @if(!empty($opt['icon'])) <x-shared.icon :name="$opt['icon']" size="12"/> @endif
            {{ $opt['label'] }}
        </button>
    @endforeach
    @if($name)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}"/>
    @endif
</div>
