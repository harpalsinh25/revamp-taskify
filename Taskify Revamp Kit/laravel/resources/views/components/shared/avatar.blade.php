@props([
    'name' => 'User',
    'size' => 22,
    'src'  => null,
])

@php
    // Deterministic palette from name hash
    $palette = ['oklch(0.45 0.05 280)','oklch(0.45 0.06 230)','oklch(0.50 0.05 165)','oklch(0.55 0.07 80)','oklch(0.50 0.08 25)','oklch(0.40 0.04 280)','oklch(0.48 0.06 320)'];
    $idx = (array_sum(array_map('ord', str_split($name)))) % count($palette);

    $initials = collect(explode(' ', trim($name)))->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('');
    $style = "width: {$size}px; height: {$size}px; font-size: " . round($size * 0.40) . "px;";
    if (!$src) $style .= " background: {$palette[$idx]};";
@endphp

<span class="av" style="{{ $style }}" title="{{ $name }}">
    @if($src) <img src="{{ $src }}" alt="{{ $name }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover"/>
    @else {{ mb_strtoupper($initials) }}
    @endif
</span>
