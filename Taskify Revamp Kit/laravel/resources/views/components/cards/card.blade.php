@props([
    'title' => null,
    'eyebrow' => null,
    'span' => null,    // 'span-2' | 'span-3' (for grid layouts)
])

@php
    $cls = ['card'];
    if ($span) $cls[] = $span;
@endphp

<section {{ $attributes->merge(['class' => implode(' ', $cls)]) }}>
    @if($title || $eyebrow || isset($header) || isset($titleSlot))
        <header class="card-head">
            <div style="min-width: 0;">
                @if($eyebrow) <div class="card-eyebrow mono">{{ $eyebrow }}</div> @endif
                @if($title)
                    <h3 class="card-title">{{ $title }}</h3>
                @elseif(isset($titleSlot))
                    <h3 class="card-title">{{ $titleSlot }}</h3>
                @endif
            </div>
            {{ $header ?? '' }}
        </header>
    @endif

    <div @class(['card-body', 'p-0' => isset($noPad)]) @if(isset($noPad))style="padding:0"@endif>
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="card-foot">{{ $footer }}</footer>
    @endisset
</section>
