@props([
    'id',
    'position' => 'right',  // left | right
    'title'    => null,
    'size'     => '380px',
])

<div class="offcanvas-host" id="{{ $id }}" hidden data-open="false">
    <div class="overlay-backdrop" data-dismiss="offcanvas"></div>
    <aside class="offcanvas" data-pos="{{ $position }}" style="width:{{ $size }};" role="dialog" aria-modal="true">
        @if($title || isset($header))
            <header class="offcanvas-head">
                <h2 class="offcanvas-title">{{ $title ?? '' }}</h2>
                {{ $header ?? '' }}
                <button type="button" class="icon-btn" data-dismiss="offcanvas" aria-label="Close">
                    <x-shared.icon name="close" size="14"/>
                </button>
            </header>
        @endif
        <div class="offcanvas-body">
            {{ $slot }}
        </div>
        @isset($footer)
            <footer class="offcanvas-foot">{{ $footer }}</footer>
        @endisset
    </aside>
</div>
