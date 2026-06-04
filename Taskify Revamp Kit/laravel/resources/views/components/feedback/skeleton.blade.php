@props([
    'variant' => 'line',     // line | title | circle | block
    'width'   => null,
    'height'  => null,
])
@php
    $cls = ['skel', 'skel-' . $variant];
    $style = '';
    if ($width)  $style .= 'width:' . (is_numeric($width) ? $width . 'px' : $width) . ';';
    if ($height) $style .= 'height:' . (is_numeric($height) ? $height . 'px' : $height) . ';';
@endphp
<span {{ $attributes->merge(['class' => implode(' ', $cls), 'style' => $style]) }} aria-hidden="true"></span>
