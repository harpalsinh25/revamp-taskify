@props(['color' => null, 'size' => 6])
<span {{ $attributes->merge(['class' => 'dot']) }} style="@if($color)background: {{ $color }};@endif width:{{$size}}px;height:{{$size}}px;"></span>
