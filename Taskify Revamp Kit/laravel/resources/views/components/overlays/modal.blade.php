@props([
    'id',
    'size'  => 'md',     // sm | md | lg | xl
    'title' => null,
])

@php
    $dialogCls = \Illuminate\Support\Arr::toCssClasses([
        'modal-dialog',
        'modal-' . $size => $size !== 'md',
    ]);
@endphp

<div class="modal-host" id="{{ $id }}" hidden data-open="false">
    <div class="overlay-backdrop" data-dismiss="modal"></div>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="{{ $dialogCls }}">
            @if($title || isset($header) || isset($titleSlot ?? null))
                <header class="modal-head">
                    <div>
                        <h2 class="modal-title" id="{{ $id }}-title">{{ $title ?? ($titleSlot ?? '') }}</h2>
                        @isset($subtitle) <div class="modal-sub">{{ $subtitle }}</div> @endisset
                    </div>
                    <button type="button" class="icon-btn" data-dismiss="modal" aria-label="Close">
                        <x-shared.icon name="close" size="14"/>
                    </button>
                </header>
            @endif
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <footer class="modal-foot">{{ $footer }}</footer>
            @endisset
        </div>
    </div>
</div>
