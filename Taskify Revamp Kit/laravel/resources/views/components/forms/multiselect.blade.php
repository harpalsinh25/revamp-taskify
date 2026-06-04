@props([
    'name'        => null,
    'endpoint'    => null,   // ajax endpoint that returns {results: [{value,label}]}
    'options'     => null,   // local static array — same shape
    'placeholder' => 'Search…',
    'values'      => [],     // pre-selected values
])

@php
    $id = uniqid('ms-');
    $selectedOpts = collect($options ?? [])->whereIn('value', $values);
@endphp

<div class="multiselect dropdown" data-multiselect
     id="{{ $id }}"
     @if($endpoint) data-endpoint="{{ $endpoint }}" @endif
     @if($options) data-local='@json($options)' @endif
     data-name="{{ $name }}[]">
    <div class="tag-strip">
        @foreach($selectedOpts as $opt)
            <span class="tag-token" data-value="{{ $opt['value'] }}">
                <span>{{ $opt['label'] }}</span>
                <button type="button" aria-label="Remove">×</button>
                <input type="hidden" name="{{ $name }}[]" value="{{ $opt['value'] }}"/>
            </span>
        @endforeach
        <input type="text" placeholder="{{ $placeholder }}"/>
    </div>
    <div class="dropdown-menu" style="position:absolute;left:0;right:0;top:calc(100% + 4px);"></div>
</div>
