@props(['vertical' => false])
@if($vertical)
    <span class="cbar-divider" {{ $attributes }}></span>
@else
    <hr {{ $attributes->merge(['class' => 'divider', 'style' => 'border:none;height:1px;background:var(--line);margin:8px 0;']) }}/>
@endif
