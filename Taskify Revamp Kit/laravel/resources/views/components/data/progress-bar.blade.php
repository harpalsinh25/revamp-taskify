@props([
    'value' => 0,        // 0–100
    'color' => 'var(--signal)',
    'showLabel' => false,
])
<div class="bar">
    <div class="bar-fill" style="width: {{ max(0, min(100, $value)) }}%; background: {{ $color }};"></div>
</div>
@if($showLabel)
    <span class="mono txt-xs" style="color:var(--fg-3);">{{ $value }}%</span>
@endif
